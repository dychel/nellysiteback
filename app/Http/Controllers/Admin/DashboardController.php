<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Order;
use App\Models\SharedCart;
use App\Models\Departure;
use App\Models\User;
use App\Models\SupportTicket;
use App\Models\SurveyAnswer;
use App\Models\Region;
use App\Models\OrderDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $filter = $request->get('filter', '');
        $type = $request->get('type', '');
        
        $stats = [
            // Anciennes statistiques
            'totalProducts' => Product::count(),
            'totalOrders' => Order::count(),
            'totalSharedCarts' => SharedCart::count(),
            'totalDepartures' => Departure::count(),
            'recentOrders' => Order::with(['user', 'orderDetails.product'])
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get(),
            
            // Nouvelles statistiques
            'uniqueVisitorsToday' => $this->getUniqueVisitorsToday(),
            'validatedOrders' => $this->getValidatedOrdersCount(),
            'pendingOrders' => $this->getPendingOrdersCount(),
            'surveysCount' => $this->getSurveysCount(),
            'campaignsCount' => $this->getCampaignsCount(),
            'maleUsers' => $this->getMaleUsersCount(),
            'femaleUsers' => $this->getFemaleUsersCount(),
            'supportTickets' => $this->getSupportTicketsCount(),
            'topProductsCount' => $this->getTopProductsCount(),
            'lowProductsCount' => $this->getLowProductsCount(),
            'topRegionSales' => $this->getTopRegionSales(),
            'lowRegionSales' => $this->getLowRegionSales(),
            'topPlatformVisits' => $this->getTopPlatformVisits(),
            
            // Données filtrées
            'filteredData' => $this->getFilteredData($filter, $type),
            'currentFilter' => $filter,
            'currentType' => $type
        ];

        return view('admin.dashboard.index', $stats);
    }

    private function getUniqueVisitorsToday()
    {
        return User::whereDate('last_login_at', today())->count();
    }

    private function getValidatedOrdersCount()
    {
        return Order::where('is_paid', true)->count();
    }

    private function getPendingOrdersCount()
    {
        return Order::where('is_paid', false)->count();
    }

    private function getSurveysCount()
    {
        return SurveyAnswer::count();
    }

    private function getCampaignsCount()
    {
        return 0; // À adapter avec un modèle Campaign
    }

    private function getMaleUsersCount()
    {
        return User::where('gender', 'male')->count();
    }

    private function getFemaleUsersCount()
    {
        return User::where('gender', 'female')->count();
    }

    private function getSupportTicketsCount()
    {
        return SupportTicket::count();
    }

    private function getTopProductsCount()
    {
        return Product::whereHas('orderDetails')
            ->withCount(['orderDetails as sales_count' => function($query) {
                $query->select(DB::raw('COALESCE(SUM(quantity), 0)'));
            }])
            ->orderBy('sales_count', 'desc')
            ->limit(5)
            ->count();
    }

    private function getLowProductsCount()
    {
        return Product::whereHas('orderDetails')
            ->withCount(['orderDetails as sales_count' => function($query) {
                $query->select(DB::raw('COALESCE(SUM(quantity), 0)'));
            }])
            ->orderBy('sales_count', 'asc')
            ->limit(5)
            ->count();
    }

    private function getTopRegionSales()
    {
        $region = Region::withCount(['products as orders_count' => function($query) {
            $query->select(DB::raw('COUNT(order_details.id)'))
                  ->join('order_details', 'products.id', '=', 'order_details.product_id');
        }])
        ->orderBy('orders_count', 'desc')
        ->first();
        
        return $region ? $region->orders_count : 0;
    }

    private function getLowRegionSales()
    {
        $region = Region::withCount(['products as orders_count' => function($query) {
            $query->select(DB::raw('COUNT(order_details.id)'))
                  ->join('order_details', 'products.id', '=', 'order_details.product_id');
        }])
        ->orderBy('orders_count', 'asc')
        ->first();
        
        return $region ? $region->orders_count : 0;
    }

    private function getTopPlatformVisits()
    {
        return User::whereNotNull('last_login_at')->count();
    }

    private function getFilteredData($filter, $type = '')
    {
        switch ($filter) {
            case 'visitors':
                return User::whereDate('last_login_at', today())
                    ->orderBy('last_login_at', 'desc')
                    ->get(['id', 'first_name', 'last_name', 'email', 'phone', 'last_login_at']);
                
            case 'orders':
                $query = Order::with(['user:id,first_name,last_name', 'orderDetails.product']);
                
                if ($type === 'validated') {
                    $query->where('is_paid', true);
                } elseif ($type === 'pending') {
                    $query->where('is_paid', false);
                }
                
                return $query->orderBy('created_at', 'desc')->get();
                
            case 'surveys':
                return SurveyAnswer::with('user:id,first_name,last_name')
                    ->orderBy('created_at', 'desc')
                    ->get();
                
            case 'users':
                $query = User::query();
                
                if ($type === 'male') {
                    $query->where('gender', 'male');
                } elseif ($type === 'female') {
                    $query->where('gender', 'female');
                }
                
                return $query->orderBy('created_at', 'desc')
                    ->get(['id', 'first_name', 'last_name', 'email', 'phone', 'gender', 'created_at']);
                
            case 'support':
                return SupportTicket::with('user:id,first_name,last_name')
                    ->orderBy('created_at', 'desc')
                    ->get();
                
            case 'products':
                $query = Product::whereHas('orderDetails');
                
                if ($type === 'top') {
                    $query->withCount(['orderDetails as sales_count' => function($query) {
                        $query->select(DB::raw('COALESCE(SUM(quantity), 0)'));
                    }])->orderBy('sales_count', 'desc');
                } elseif ($type === 'low') {
                    $query->withCount(['orderDetails as sales_count' => function($query) {
                        $query->select(DB::raw('COALESCE(SUM(quantity), 0)'));
                    }])->orderBy('sales_count', 'asc');
                } else {
                    $query->withCount(['orderDetails as sales_count' => function($query) {
                        $query->select(DB::raw('COALESCE(SUM(quantity), 0)'));
                    }])->orderBy('sales_count', 'desc');
                }
                
                return $query->limit(20)->get(['id', 'name', 'price', 'illustration', 'sales_count']);
                
            case 'regions':
                return Region::withCount(['products as orders_count' => function($query) {
                    $query->select(DB::raw('COUNT(order_details.id)'))
                          ->join('order_details', 'products.id', '=', 'order_details.product_id');
                }])
                ->orderBy('orders_count', $type === 'low' ? 'asc' : 'desc')
                ->get();
                
            default:
                return collect();
        }
    }
}