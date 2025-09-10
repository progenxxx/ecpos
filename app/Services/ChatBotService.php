<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ChatBotService
{
    protected $client;
    protected $anthropicApiKey;
    protected $openaiApiKey;
    protected $anthropicModel;
    protected $openaiModel;

    public function __construct()
    {
        $this->client = new Client([
            'verify' => false, // Disable SSL verification for local development on Windows
            'timeout' => 30,
            'connect_timeout' => 10
        ]);
        $this->anthropicApiKey = env('ANTHROPIC_API_KEY');
        $this->openaiApiKey = env('OPENAI_API_KEY');
        $this->anthropicModel = env('ANTHROPIC_MODEL', 'claude-3-5-sonnet-20241022');
        $this->openaiModel = env('OPENAI_MODEL', 'gpt-3.5-turbo');
    }

    public function sendMessage($message, $context = [])
    {
        if (!$this->anthropicApiKey && !$this->openaiApiKey) {
            return [
                'success' => false,
                'message' => 'No AI API keys are configured. Please contact your administrator.',
                'response' => null
            ];
        }

        // Detect if user is asking about a specific store
        $specificStore = $this->detectSpecificStore($message);
        
        // Get sales context for the chatbot (with store filtering if needed)
        $salesContext = $this->getSalesContext($specificStore);
        
        // Build the system message with current sales data
        $systemMessage = $this->buildSystemMessage($salesContext, $specificStore);

        // Try Anthropic API first
        if ($this->anthropicApiKey) {
            $response = $this->tryAnthropicAPI($message, $systemMessage);
            if ($response['success']) {
                return $response;
            }
            
            Log::warning('Anthropic API failed, trying OpenAI as fallback', [
                'anthropic_error' => $response['message']
            ]);
        }

        // Fallback to OpenAI API
        if ($this->openaiApiKey) {
            return $this->tryOpenAIAPI($message, $systemMessage, $salesContext);
        }

        return [
            'success' => false,
            'message' => 'All AI services are unavailable. Please try again later.',
            'response' => null
        ];
    }

    protected function tryAnthropicAPI($message, $systemMessage)
    {
        try {
            $response = $this->client->post('https://api.anthropic.com/v1/messages', [
                'headers' => [
                    'x-api-key' => $this->anthropicApiKey,
                    'Content-Type' => 'application/json',
                    'anthropic-version' => '2023-06-01'
                ],
                'json' => [
                    'model' => $this->anthropicModel,
                    'max_tokens' => 1000,
                    'system' => $systemMessage,
                    'messages' => [
                        [
                            'role' => 'user',
                            'content' => $message
                        ]
                    ]
                ]
            ]);

            $body = json_decode($response->getBody(), true);
            
            if (isset($body['content'][0]['text'])) {
                return [
                    'success' => true,
                    'message' => 'Response generated successfully (Anthropic)',
                    'response' => trim($body['content'][0]['text'])
                ];
            }

            return [
                'success' => false,
                'message' => 'No response generated from Anthropic',
                'response' => null
            ];

        } catch (RequestException $e) {
            $errorResponse = $e->hasResponse() ? $e->getResponse()->getBody()->getContents() : null;
            
            Log::error('Anthropic API Error', [
                'message' => $e->getMessage(),
                'response' => $errorResponse
            ]);

            return [
                'success' => false,
                'message' => 'Anthropic API failed: ' . $e->getMessage(),
                'response' => null
            ];
        } catch (\Exception $e) {
            Log::error('Anthropic Service Error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'message' => 'Anthropic service error: ' . $e->getMessage(),
                'response' => null
            ];
        }
    }

    protected function tryOpenAIAPI($message, $systemMessage, $salesContext)
    {
        try {
            $response = $this->client->post('https://api.openai.com/v1/chat/completions', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->openaiApiKey,
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'model' => $this->openaiModel,
                    'messages' => [
                        [
                            'role' => 'system',
                            'content' => $systemMessage
                        ],
                        [
                            'role' => 'user',
                            'content' => $message
                        ]
                    ],
                    'max_tokens' => 1000,
                    'temperature' => 0.7,
                ]
            ]);

            $body = json_decode($response->getBody(), true);
            
            if (isset($body['choices'][0]['message']['content'])) {
                return [
                    'success' => true,
                    'message' => 'Response generated successfully (OpenAI)',
                    'response' => trim($body['choices'][0]['message']['content'])
                ];
            }

            return [
                'success' => false,
                'message' => 'No response generated from OpenAI',
                'response' => null
            ];

        } catch (RequestException $e) {
            $errorResponse = $e->hasResponse() ? $e->getResponse()->getBody()->getContents() : null;
            
            Log::error('OpenAI API Error', [
                'message' => $e->getMessage(),
                'response' => $errorResponse
            ]);

            // Check if it's a quota exceeded error and provide fallback response
            if (str_contains($e->getMessage(), '429') || str_contains($errorResponse ?? '', 'quota')) {
                return [
                    'success' => true,
                    'message' => 'Response generated from local sales analysis',
                    'response' => $this->getFallbackResponse($message, $salesContext)
                ];
            }

            return [
                'success' => false,
                'message' => 'Failed to connect to OpenAI service. Please try again later.',
                'response' => null
            ];
        } catch (\Exception $e) {
            Log::error('OpenAI Service Error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'message' => 'OpenAI service error occurred. Please try again later.',
                'response' => null
            ];
        }
    }

    protected function getSalesContext($specificStore = null)
    {
        try {
            $startDate = Carbon::now()->subDays(30);
            $endDate = Carbon::now();
            $today = Carbon::today();
            $yesterday = Carbon::yesterday();

            // Use HomeController's calculateMetrics method logic for accurate dashboard data
            $dashboardMetrics = $this->getDashboardMetrics($startDate, $endDate, $specificStore);
            
            // Get today's specific data
            $todayData = $this->getTodayMetrics($specificStore);
            
            // Get yesterday's data for comparison
            $yesterdayData = $this->getYesterdayMetrics($specificStore);
            
            // Get top products data matching dashboard
            $topProductsData = $this->getTopProductsData($startDate, $endDate, $specificStore);
            
            // Get top stores data matching dashboard (don't filter if asking about specific store)
            $topStoresData = $this->getTopStoresData($startDate, $endDate, $specificStore ? null : $specificStore);
            
            // Get hourly sales pattern
            $hourlySalesData = $this->getHourlySalesData($startDate, $endDate, $specificStore);
            
            // Get waste analysis data
            $wasteData = $this->getWasteAnalysisData($startDate, $endDate, $specificStore);
            
            // Get advanced analytics
            $advancedAnalytics = $this->getAdvancedAnalytics($startDate, $endDate, $specificStore);

            return [
                'dashboard_metrics' => $dashboardMetrics,
                'today_data' => $todayData,
                'yesterday_data' => $yesterdayData,
                'top_products' => $topProductsData,
                'top_stores' => $topStoresData,
                'hourly_sales' => $hourlySalesData,
                'waste_analysis' => $wasteData,
                'advanced_analytics' => $advancedAnalytics,
                'period_info' => [
                    'start_date' => $startDate->format('Y-m-d'),
                    'end_date' => $endDate->format('Y-m-d'),
                    'today' => $today->format('Y-m-d'),
                    'yesterday' => $yesterday->format('Y-m-d')
                ]
            ];

        } catch (\Exception $e) {
            Log::error('Failed to fetch sales context', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return [
                'dashboard_metrics' => null,
                'today_data' => null,
                'yesterday_data' => null,
                'top_products' => [],
                'top_stores' => [],
                'hourly_sales' => [],
                'waste_analysis' => null,
                'advanced_analytics' => null,
                'period_info' => [
                    'start_date' => Carbon::now()->subDays(30)->format('Y-m-d'),
                    'end_date' => Carbon::now()->format('Y-m-d'),
                    'today' => Carbon::today()->format('Y-m-d'),
                    'yesterday' => Carbon::yesterday()->format('Y-m-d')
                ]
            ];
        }
    }

    protected function detectSpecificStore($message)
    {
        $message = strtoupper(trim($message));
        
        // List of known stores - these should match your database values
        $knownStores = ['CAMILING', 'COMMUNITY', 'DAU', 'DUMMY', 'FLORIDA', 'GYRIES', 'LAPAZ', 'VICTORIA'];
        
        foreach ($knownStores as $store) {
            if (str_contains($message, $store)) {
                return $store;
            }
        }
        
        return null;
    }

    protected function getDashboardMetrics($startDate, $endDate, $specificStore = null)
    {
        // Mirror HomeController's calculateMetrics method
        $query = DB::table('rbotransactiontables')
            ->whereDate('createddate', '>=', $startDate)
            ->whereDate('createddate', '<=', $endDate);
            
        if ($specificStore) {
            $query->where('store', $specificStore);
        }
        
        $baseMetrics = $query->selectRaw('
                COALESCE(SUM(grossamount), 0) as totalGross,
                COALESCE(SUM(netamount), 0) as totalNetsales,
                COALESCE(SUM(totaldiscamount), 0) as totalDiscount,
                COALESCE(SUM(costamount), 0) as totalCost,
                COALESCE(SUM(netamount - netamountnotincltax), 0) as totalVat,
                COALESCE(SUM(netamountnotincltax), 0) as totalVatableSales,
                COALESCE(SUM(cash), 0) as totalCash,
                COALESCE(SUM(gcash), 0) as totalGcash,
                COALESCE(SUM(paymaya), 0) as totalPaymaya,
                COALESCE(SUM(card), 0) as totalCard,
                COALESCE(SUM(loyaltycard), 0) as totalLoyaltyCard,
                COALESCE(SUM(foodpanda), 0) as totalFoodPanda,
                COALESCE(SUM(grabfood), 0) as totalGrabFood,
                COALESCE(SUM(representation), 0) as totalRepresentation
            ')
            ->first();

        $transactionsQuery = DB::table('rbotransactionsalestrans')
            ->whereDate('createddate', '>=', $startDate)
            ->whereDate('createddate', '<=', $endDate);
        if ($specificStore) {
            $transactionsQuery->where('store', $specificStore);
        }
        $totalTransactions = $transactionsQuery->count();

        $deliveriesQuery = DB::table('receivedordertrans')
            ->whereDate('TRANSDATE', '>=', $startDate)
            ->whereDate('TRANSDATE', '<=', $endDate);
        if ($specificStore) {
            $deliveriesQuery->where('STORENAME', $specificStore);
        }
        $totalReceivedDeliveries = $deliveriesQuery->count();

        $wasteQuery = DB::table('wastedeclarationtrans')
            ->whereDate('TRANSDATE', '>=', $startDate)
            ->whereDate('TRANSDATE', '<=', $endDate);
        if ($specificStore) {
            $wasteQuery->where('STORENAME', $specificStore);
        }
        $totalWaste = $wasteQuery->count();

        $todayTransactionsQuery = DB::table('rbotransactiontables')
            ->whereDate('createddate', Carbon::today());
        if ($specificStore) {
            $todayTransactionsQuery->where('store', $specificStore);
        }
        $todayTransactions = $todayTransactionsQuery->count();

        return [
            'totalGross' => (float) ($baseMetrics->totalGross ?? 0),
            'totalNetsales' => (float) ($baseMetrics->totalNetsales ?? 0),
            'totalDiscount' => (float) ($baseMetrics->totalDiscount ?? 0),
            'totalCost' => (float) ($baseMetrics->totalCost ?? 0),
            'totalVat' => (float) ($baseMetrics->totalVat ?? 0),
            'totalVatableSales' => (float) ($baseMetrics->totalVatableSales ?? 0),
            'totalTransactions' => $totalTransactions,
            'totalReceivedDeliveries' => $totalReceivedDeliveries,
            'totalWaste' => $totalWaste,
            'todayTransactions' => $todayTransactions,
            'paymentBreakdown' => [
                'cash' => round($baseMetrics->totalCash ?? 0),
                'gcash' => round($baseMetrics->totalGcash ?? 0),
                'paymaya' => round($baseMetrics->totalPaymaya ?? 0),
                'card' => round($baseMetrics->totalCard ?? 0),
                'loyaltyCard' => round($baseMetrics->totalLoyaltyCard ?? 0),
                'foodPanda' => round($baseMetrics->totalFoodPanda ?? 0),
                'grabFood' => round($baseMetrics->totalGrabFood ?? 0),
                'representation' => round($baseMetrics->totalRepresentation ?? 0)
            ]
        ];
    }

    protected function getTodayMetrics($specificStore = null)
    {
        $today = Carbon::today();
        
        $query = DB::table('rbotransactiontables')
            ->whereDate('createddate', $today);
            
        if ($specificStore) {
            $query->where('store', $specificStore);
        }
        
        return $query->selectRaw('
                COALESCE(SUM(grossamount), 0) as gross_sales,
                COALESCE(SUM(netamount), 0) as net_sales,
                COALESCE(SUM(totaldiscamount), 0) as total_discount,
                COUNT(*) as transaction_count,
                COUNT(DISTINCT store) as active_stores,
                AVG(grossamount) as avg_transaction_value
            ')
            ->first();
    }

    protected function getYesterdayMetrics($specificStore = null)
    {
        $yesterday = Carbon::yesterday();
        
        $query = DB::table('rbotransactiontables')
            ->whereDate('createddate', $yesterday);
            
        if ($specificStore) {
            $query->where('store', $specificStore);
        }
        
        return $query->selectRaw('
                COALESCE(SUM(grossamount), 0) as gross_sales,
                COALESCE(SUM(netamount), 0) as net_sales,
                COALESCE(SUM(totaldiscamount), 0) as total_discount,
                COUNT(*) as transaction_count
            ')
            ->first();
    }

    protected function getTopProductsData($startDate, $endDate, $specificStore = null)
    {
        // Mirror HomeController's getTopBottomProducts logic
        $query = DB::table('rbotransactionsalestrans as r')
            ->select(
                'r.itemname',
                'r.itemid',
                DB::raw('ABS(SUM(r.qty)) as total_quantity'),
                DB::raw('ABS(SUM(r.netamount)) as total_sales'),
                DB::raw('COUNT(DISTINCT r.store) as store_count'),
                DB::raw('COUNT(DISTINCT r.transactionid) as transaction_frequency')
            )
            ->whereDate('r.createddate', '>=', $startDate)
            ->whereDate('r.createddate', '<=', $endDate);
            
        if ($specificStore) {
            $query->where('r.store', $specificStore);
        }
        
        return $query->where(function($q) {
                $q->where('r.qty', '>', 0)
                  ->orWhere('r.netamount', '>', 0);
            })
            ->groupBy('r.itemname', 'r.itemid')
            ->havingRaw('SUM(ABS(r.qty)) > 0 OR SUM(ABS(r.netamount)) > 0')
            ->orderByDesc('total_sales')
            ->limit(10)
            ->get();
    }

    protected function getTopStoresData($startDate, $endDate, $specificStore = null)
    {
        $query = DB::table('rbotransactionsalestrans as r')
            ->select(
                'r.store',
                DB::raw('SUM(ABS(r.grossamount)) as total_sales'),
                DB::raw('COUNT(DISTINCT r.transactionid) as transaction_count'),
                DB::raw('COUNT(DISTINCT r.itemid) as unique_items'),
                DB::raw('SUM(ABS(r.qty)) as total_quantity')
            )
            ->whereDate('r.createddate', '>=', $startDate)
            ->whereDate('r.createddate', '<=', $endDate);
            
        if ($specificStore) {
            $query->where('r.store', $specificStore);
        }
        
        return $query->groupBy('r.store')
            ->orderByDesc('total_sales')
            ->limit(10)
            ->get();
    }

    protected function getHourlySalesData($startDate, $endDate, $specificStore = null)
    {
        $query = DB::table('rbotransactionsalestrans as r')
            ->select(
                DB::raw('HOUR(r.createddate) as hour'),
                DB::raw('SUM(ABS(r.grossamount)) as total_sales'),
                DB::raw('SUM(ABS(r.qty)) as total_quantity'),
                DB::raw('COUNT(DISTINCT r.transactionid) as transaction_count')
            )
            ->whereDate('r.createddate', '>=', $startDate)
            ->whereDate('r.createddate', '<=', $endDate);
            
        if ($specificStore) {
            $query->where('r.store', $specificStore);
        }
        
        return $query->groupBy(DB::raw('HOUR(r.createddate)'))
            ->orderBy('hour')
            ->get();
    }

    protected function getWasteAnalysisData($startDate, $endDate, $specificStore = null)
    {
        try {
            $query = DB::table('stockcountingtrans as s')
                ->join('inventtables as i', 's.ITEMID', '=', 'i.ITEMID')
                ->select(
                    's.ITEMID',
                    'i.itemname',
                    's.WASTETYPE as waste_type',
                    DB::raw('SUM(ABS(s.WASTECOUNT)) as total_waste_quantity'),
                    DB::raw('COUNT(DISTINCT s.STORENAME) as store_count')
                )
                ->whereBetween('s.TRANSDATE', [
                    $startDate->format('Y-m-d') . ' 00:00:00',
                    $endDate->format('Y-m-d') . ' 23:59:59'
                ])
                ->where('s.WASTECOUNT', '>', 0)
                ->whereNotNull('s.WASTETYPE');
                
            if ($specificStore) {
                $query->where('s.STORENAME', $specificStore);
            }
            
            return $query->groupBy('s.ITEMID', 'i.itemname', 's.WASTETYPE')
                ->orderByDesc('total_waste_quantity')
                ->limit(10)
                ->get();
        } catch (\Exception $e) {
            Log::error('Waste analysis data error', ['message' => $e->getMessage()]);
            return collect();
        }
    }

    protected function getAdvancedAnalytics($startDate, $endDate, $specificStore = null)
    {
        try {
            // Sales trend analysis
            $query = DB::table('rbotransactionsalestrans as r')
                ->select(
                    DB::raw('DATE(r.createddate) as date'),
                    DB::raw('SUM(ABS(r.grossamount)) as gross_sales'),
                    DB::raw('SUM(ABS(r.netamount)) as net_sales'),
                    DB::raw('COUNT(DISTINCT r.transactionid) as transactions')
                )
                ->whereDate('r.createddate', '>=', $startDate)
                ->whereDate('r.createddate', '<=', $endDate);
                
            if ($specificStore) {
                $query->where('r.store', $specificStore);
            }
            
            $salesTrend = $query->groupBy(DB::raw('DATE(r.createddate)'))
                ->orderBy('date')
                ->get();

            return [
                'sales_trend' => $salesTrend,
                'trend_analysis' => $this->calculateTrendAnalysis($salesTrend)
            ];
        } catch (\Exception $e) {
            Log::error('Advanced analytics error', ['message' => $e->getMessage()]);
            return null;
        }
    }

    protected function calculateTrendAnalysis($salesTrend)
    {
        if ($salesTrend->count() < 2) {
            return null;
        }
        
        $firstWeek = $salesTrend->take(7)->sum('gross_sales');
        $lastWeek = $salesTrend->skip(max(0, $salesTrend->count() - 7))->sum('gross_sales');
        
        $growthRate = $firstWeek > 0 ? (($lastWeek - $firstWeek) / $firstWeek) * 100 : 0;
        
        return [
            'growth_rate' => round($growthRate, 2),
            'trend_direction' => $growthRate > 5 ? 'increasing' : ($growthRate < -5 ? 'decreasing' : 'stable'),
            'average_daily_sales' => round($salesTrend->avg('gross_sales'), 2),
            'peak_day' => $salesTrend->sortByDesc('gross_sales')->first()
        ];
    }

    protected function buildSystemMessage($salesContext, $specificStore = null)
    {
        $dashboardMetrics = $salesContext['dashboard_metrics'];
        $todayData = $salesContext['today_data'];
        $yesterdayData = $salesContext['yesterday_data'];
        $periodInfo = $salesContext['period_info'];
        
        // Calculate key metrics
        $todayGross = $todayData->gross_sales ?? 0;
        $yesterdayGross = $yesterdayData->gross_sales ?? 0;
        $periodGross = $dashboardMetrics['totalGross'] ?? 0;
        
        $salesTrend = 'steady';
        if ($yesterdayGross > 0) {
            $changePercent = (($todayGross - $yesterdayGross) / $yesterdayGross) * 100;
            if ($changePercent > 5) $salesTrend = 'increasing';
            elseif ($changePercent < -5) $salesTrend = 'decreasing';
        }

        // Format top stores data
        $topStoresList = collect($salesContext['top_stores'])->map(function($store) {
            return $store->store . ' (₱' . number_format($store->total_sales, 2) . ')';
        })->take(3)->implode(', ');

        // Format top products data
        $topProductsList = collect($salesContext['top_products'])->map(function($product) {
            return $product->itemname . ' (' . number_format($product->total_quantity) . ' units)';
        })->take(5)->implode(', ');

        // Payment method breakdown
        $paymentBreakdown = $dashboardMetrics['paymentBreakdown'] ?? [];
        $topPaymentMethod = collect($paymentBreakdown)->sortDesc()->keys()->first();
        
        // Advanced analytics
        $advancedAnalytics = $salesContext['advanced_analytics'];
        $trendDirection = $advancedAnalytics['trend_analysis']['trend_direction'] ?? 'stable';
        $growthRate = $advancedAnalytics['trend_analysis']['growth_rate'] ?? 0;

        $storeFilterText = $specificStore ? " (FILTERED FOR STORE: {$specificStore})" : "";
        $storeContext = $specificStore ? "\n\n🏪 **IMPORTANT**: All data below is specifically filtered for {$specificStore} store only. When asked about specific stores, provide accurate data for that store." : "";

        return "You are a professional sales analyst and business advisor for an ECPOS (Electronic Cash Point of Sale) system. You have access to comprehensive real-time dashboard data and provide accurate, data-driven insights.

=== CURRENT DASHBOARD DATA (Period: {$periodInfo['start_date']} to {$periodInfo['end_date']}){$storeFilterText} ==={$storeContext}

📊 **KEY METRICS:**
- Period Total Sales: ₱" . number_format($periodGross, 2) . "
- Total Net Sales: ₱" . number_format($dashboardMetrics['totalNetsales'] ?? 0, 2) . "
- Total Discounts: ₱" . number_format($dashboardMetrics['totalDiscount'] ?? 0, 2) . "
- Total Transactions: " . number_format($dashboardMetrics['totalTransactions'] ?? 0) . "
- VAT Amount: ₱" . number_format($dashboardMetrics['totalVat'] ?? 0, 2) . "
- Cost Amount: ₱" . number_format($dashboardMetrics['totalCost'] ?? 0, 2) . "

📈 **TODAY'S PERFORMANCE:**
- Today's Sales: ₱" . number_format($todayGross, 2) . "
- Yesterday's Sales: ₱" . number_format($yesterdayGross, 2) . "
- Daily Trend: {$salesTrend} (" . ($growthRate >= 0 ? '+' : '') . number_format($growthRate, 1) . "%)
- Today's Transactions: " . number_format($todayData->transaction_count ?? 0) . "
- Active Stores Today: " . ($todayData->active_stores ?? 0) . "
- Avg Transaction Value: ₱" . number_format($todayData->avg_transaction_value ?? 0, 2) . "

💳 **PAYMENT METHODS BREAKDOWN:**
- Cash: ₱" . number_format($paymentBreakdown['cash'] ?? 0, 2) . "
- GCash: ₱" . number_format($paymentBreakdown['gcash'] ?? 0, 2) . "
- Card: ₱" . number_format($paymentBreakdown['card'] ?? 0, 2) . "
- Food Delivery: ₱" . number_format(($paymentBreakdown['foodPanda'] ?? 0) + ($paymentBreakdown['grabFood'] ?? 0), 2) . "
- Most Used: " . ucfirst(str_replace('_', ' ', $topPaymentMethod ?? 'Cash')) . "

🏪 **TOP PERFORMING STORES:** {$topStoresList}

🛍️ **BEST SELLING PRODUCTS:** {$topProductsList}

📦 **OPERATIONS DATA:**
- Received Deliveries: " . number_format($dashboardMetrics['totalReceivedDeliveries'] ?? 0) . "
- Waste Transactions: " . number_format($dashboardMetrics['totalWaste'] ?? 0) . "

📊 **BUSINESS TREND:** Currently {$trendDirection} with {$growthRate}% growth rate

=== YOUR CAPABILITIES ===
You can analyze and provide insights on:
✅ Sales performance & comparisons
✅ Product analysis (top/bottom performers)
✅ Store performance rankings  
✅ Payment method preferences
✅ Hourly sales patterns
✅ Waste analysis & cost reduction
✅ Financial metrics (VAT, discounts, costs)
✅ Trend analysis & forecasting
✅ Strategic business recommendations

=== RESPONSE GUIDELINES ===
- Always use the EXACT data provided above
- Reference specific metrics and figures from dashboard
- Provide actionable, data-driven recommendations
- Use Philippine Peso (₱) format for all currency
- Be professional but conversational
- Focus on practical business insights
- Compare current vs historical performance when relevant

**Important:** Your responses must align with the actual ECPOS dashboard data structure and metrics. All numbers should match what users see on their dashboard.";
    }

    protected function getFallbackResponse($message, $salesContext)
    {
        $originalMessage = $message;
        $message = strtolower(trim($message));
        $specificStore = $this->detectSpecificStore($originalMessage);
        $dashboardMetrics = $salesContext['dashboard_metrics'];
        $todayData = $salesContext['today_data'];
        $yesterdayData = $salesContext['yesterday_data'];
        
        // Calculate performance indicators based on actual dashboard data
        $todayGross = $todayData->gross_sales ?? 0;
        $yesterdayGross = $yesterdayData->gross_sales ?? 0;
        $periodGross = $dashboardMetrics['totalGross'] ?? 0;
        $periodNet = $dashboardMetrics['totalNetsales'] ?? 0;
        
        $dailyChange = $yesterdayGross > 0 ? (($todayGross - $yesterdayGross) / $yesterdayGross) * 100 : 0;
        $avgTransactionValue = $todayData->avg_transaction_value ?? 0;
        
        // SPECIFIC STORE SALES ANALYSIS
        if ($specificStore && (str_contains($message, 'sales') || str_contains($message, 'today') || str_contains($message, 'how much'))) {
            $storeContext = $specificStore ? " for {$specificStore} store" : "";
            $response = "📊 **{$specificStore} Store Sales Performance**\n\n";
            $response .= "💰 **Today's Sales{$storeContext}:**\n";
            $response .= "• Gross Sales: ₱" . number_format($todayGross, 2) . "\n";
            $response .= "• Net Sales: ₱" . number_format($todayData->net_sales ?? 0, 2) . "\n";
            $response .= "• Total Discounts: ₱" . number_format($todayData->total_discount ?? 0, 2) . "\n";
            $response .= "• Transactions: " . number_format($todayData->transaction_count ?? 0) . "\n";
            $response .= "• Avg Transaction: ₱" . number_format($avgTransactionValue, 2) . "\n\n";
            
            $response .= "📈 **Daily Comparison{$storeContext}:**\n";
            $response .= "• Yesterday: ₱" . number_format($yesterdayGross, 2) . "\n";
            $response .= "• Today: ₱" . number_format($todayGross, 2) . "\n";
            $response .= "• Change: " . ($dailyChange >= 0 ? '+' : '') . number_format($dailyChange, 1) . "%\n\n";
            
            $periodGross = $dashboardMetrics['totalGross'] ?? 0;
            $response .= "📊 **Period Performance (Last 30 Days){$storeContext}:**\n";
            $response .= "• Total Sales: ₱" . number_format($periodGross, 2) . "\n";
            $response .= "• Total Transactions: " . number_format($dashboardMetrics['totalTransactions'] ?? 0) . "\n\n";
            
            $response .= "💡 **{$specificStore} Store Insight**: " . ($dailyChange > 5 ? "Strong performance today!" : 
                        ($dailyChange > 0 ? "Positive trend for the store." : 
                         ($dailyChange > -10 ? "Normal variation for the store." : "Consider reviewing store operations.")));
            
            return $response;
        }
        
        // TODAY'S PERFORMANCE ANALYSIS - Based on Dashboard Data
        if (str_contains($message, 'today') || str_contains($message, 'performance') || str_contains($message, 'status') || str_contains($message, 'dashboard')) {
            $trend = $dailyChange > 5 ? 'significantly higher' : ($dailyChange > 0 ? 'higher' : ($dailyChange < -5 ? 'significantly lower' : 'similar'));
            $paymentBreakdown = $dashboardMetrics['paymentBreakdown'] ?? [];
            
            $response = "📊 **Dashboard Performance Overview**\n\n";
            $response .= "💰 **Today's Performance:**\n";
            $response .= "• Gross Sales: ₱" . number_format($todayGross, 2) . "\n";
            $response .= "• Net Sales: ₱" . number_format($todayData->net_sales ?? 0, 2) . "\n";
            $response .= "• Total Discounts: ₱" . number_format($todayData->total_discount ?? 0, 2) . "\n";
            $response .= "• Transactions: " . number_format($todayData->transaction_count ?? 0) . "\n";
            $response .= "• Active Stores: " . ($todayData->active_stores ?? 0) . "\n";
            $response .= "• Avg Transaction: ₱" . number_format($avgTransactionValue, 2) . "\n\n";
            
            $response .= "📈 **Period Summary (Last 30 Days):**\n";
            $response .= "• Total Gross: ₱" . number_format($periodGross, 2) . "\n";
            $response .= "• Total Net: ₱" . number_format($periodNet, 2) . "\n";
            $response .= "• Total Transactions: " . number_format($dashboardMetrics['totalTransactions'] ?? 0) . "\n";
            $response .= "• Received Deliveries: " . number_format($dashboardMetrics['totalReceivedDeliveries'] ?? 0) . "\n";
            $response .= "• Waste Records: " . number_format($dashboardMetrics['totalWaste'] ?? 0) . "\n\n";
            
            if (!empty($paymentBreakdown)) {
                $response .= "💳 **Payment Methods (Period Total):**\n";
                $response .= "• Cash: ₱" . number_format($paymentBreakdown['cash'] ?? 0, 2) . "\n";
                $response .= "• GCash: ₱" . number_format($paymentBreakdown['gcash'] ?? 0, 2) . "\n";
                $response .= "• Card: ₱" . number_format($paymentBreakdown['card'] ?? 0, 2) . "\n";
                $response .= "• Food Panda: ₱" . number_format($paymentBreakdown['foodPanda'] ?? 0, 2) . "\n";
                $response .= "• Grab Food: ₱" . number_format($paymentBreakdown['grabFood'] ?? 0, 2) . "\n\n";
            }
            
            $response .= "📊 **Daily Comparison:**\n";
            $response .= "• Yesterday: ₱" . number_format($yesterdayGross, 2) . "\n";
            $response .= "• Today: ₱" . number_format($todayGross, 2) . "\n";
            $response .= "• Change: " . ($dailyChange >= 0 ? '+' : '') . number_format($dailyChange, 1) . "%\n";
            $response .= "• Trend: **{$trend}** than yesterday\n\n";
            
            $response .= "💡 **Dashboard Insight**: " . ($dailyChange > 10 ? "Excellent daily growth! Your dashboard shows strong performance." : 
                        ($dailyChange > 0 ? "Positive daily trend. Dashboard metrics are healthy." : 
                         ($dailyChange > -10 ? "Normal daily variation. Overall dashboard metrics remain stable." : "Consider reviewing dashboard metrics for improvement opportunities.")));
            
            return $response;
        }
        
        // TOP SELLING ITEMS ANALYSIS - Dashboard Aligned
        if ((str_contains($message, 'top') || str_contains($message, 'best')) && (str_contains($message, 'product') || str_contains($message, 'item') || str_contains($message, 'selling'))) {
            $topProducts = collect($salesContext['top_products'])->take(10);
            $response = "🛍️ **Top Selling Products (Dashboard Data)**\n\n";
            
            if ($topProducts->isEmpty()) {
                $response .= "No product data available for the current period.\n\n";
            } else {
                foreach ($topProducts as $index => $product) {
                    $response .= ($index + 1) . ". **{$product->itemname}**\n";
                    $response .= "   • Quantity Sold: " . number_format($product->total_quantity) . " units\n";
                    $response .= "   • Revenue: ₱" . number_format($product->total_sales, 2) . "\n";
                    $response .= "   • Store Coverage: " . number_format($product->store_count) . " stores\n";
                    $response .= "   • Transaction Frequency: " . number_format($product->transaction_frequency ?? 0) . "\n\n";
                }
                
                $totalRevenue = $topProducts->sum('total_sales');
                $totalQuantity = $topProducts->sum('total_quantity');
                $response .= "📊 **Dashboard Summary**:\n";
                $response .= "• Top 10 Products Revenue: ₱" . number_format($totalRevenue, 2) . "\n";
                $response .= "• Total Units Sold: " . number_format($totalQuantity) . "\n";
                $response .= "• Average Revenue per Product: ₱" . number_format($totalRevenue / max(1, $topProducts->count()), 2) . "\n\n";
            }
            
            $response .= "💡 **Dashboard-Based Recommendations:**\n";
            $response .= "• Monitor inventory levels for top performers\n";
            $response .= "• Analyze store coverage patterns\n";
            $response .= "• Focus marketing on high-frequency items\n";
            $response .= "• Use dashboard data to optimize product placement";
            
            return $response;
        }
        
        // STORE PERFORMANCE ANALYSIS - Dashboard Aligned
        if (str_contains($message, 'store') && (str_contains($message, 'performance') || str_contains($message, 'best') || str_contains($message, 'top'))) {
            $topStores = collect($salesContext['top_stores'])->take(8);
            $response = "🏪 **Store Performance (Dashboard Data)**\n\n";
            
            if ($topStores->isEmpty()) {
                $response .= "No store performance data available for the current period.\n\n";
            } else {
                foreach ($topStores as $index => $store) {
                    $response .= ($index + 1) . ". **{$store->store}**\n";
                    $response .= "   • Total Sales: ₱" . number_format($store->total_sales, 2) . "\n";
                    $response .= "   • Transactions: " . number_format($store->transaction_count) . "\n";
                    $response .= "   • Unique Items: " . number_format($store->unique_items) . "\n";
                    $response .= "   • Total Quantity: " . number_format($store->total_quantity) . "\n";
                    $response .= "   • Avg per Transaction: ₱" . number_format($store->total_sales / max(1, $store->transaction_count), 2) . "\n\n";
                }
                
                $totalStoreRevenue = $topStores->sum('total_sales');
                $totalTransactions = $topStores->sum('transaction_count');
                $response .= "📊 **Dashboard Store Summary**:\n";
                $response .= "• Top Stores Revenue: ₱" . number_format($totalStoreRevenue, 2) . "\n";
                $response .= "• Combined Transactions: " . number_format($totalTransactions) . "\n";
                $response .= "• Average Store Performance: ₱" . number_format($totalStoreRevenue / max(1, $topStores->count()), 2) . "\n\n";
            }
            
            $response .= "🎯 **Dashboard-Based Actions:**\n";
            $response .= "• Compare store metrics using dashboard filters\n";
            $response .= "• Analyze transaction patterns across locations\n";
            $response .= "• Monitor unique item diversity per store\n";
            $response .= "• Use dashboard data for resource allocation";
            
            return $response;
        }
        
        // HOURLY SALES PATTERN ANALYSIS - Dashboard Aligned
        if (str_contains($message, 'hour') || str_contains($message, 'time') || str_contains($message, 'peak') || str_contains($message, 'busy')) {
            $hourlyData = collect($salesContext['hourly_sales'])->sortBy('hour');
            $response = "⏰ **Hourly Sales Pattern (Dashboard Data)**\n\n";
            
            if ($hourlyData->isEmpty()) {
                $response .= "No hourly sales data available for the current period.\n\n";
            } else {
                $peakHour = $hourlyData->sortByDesc('total_sales')->first();
                $busiestHour = $hourlyData->sortByDesc('transaction_count')->first();
                
                $response .= "🔥 **Peak Performance Analysis:**\n";
                $response .= "• Highest Sales Hour: " . ($peakHour->hour ?? 'N/A') . ":00 (₱" . number_format($peakHour->total_sales ?? 0, 2) . ")\n";
                $response .= "• Busiest Transaction Hour: " . ($busiestHour->hour ?? 'N/A') . ":00 (" . ($busiestHour->transaction_count ?? 0) . " transactions)\n";
                $response .= "• Peak Quantity Hour: " . $hourlyData->sortByDesc('total_quantity')->first()->hour . ":00 (" . number_format($hourlyData->max('total_quantity')) . " items)\n\n";
                
                $totalHourlySales = $hourlyData->sum('total_sales');
                $avgHourlySales = $hourlyData->avg('total_sales');
                
                $response .= "📊 **Hourly Performance Summary:**\n";
                $response .= "• Active Hours: " . $hourlyData->count() . " hours\n";
                $response .= "• Total Hourly Sales: ₱" . number_format($totalHourlySales, 2) . "\n";
                $response .= "• Average per Hour: ₱" . number_format($avgHourlySales, 2) . "\n\n";
                
                $response .= "📈 **Top 5 Sales Hours:**\n";
                $topHours = $hourlyData->sortByDesc('total_sales')->take(5);
                foreach ($topHours as $index => $hour) {
                    $hourLabel = $hour->hour == 0 ? '12 AM' : ($hour->hour < 12 ? $hour->hour . ' AM' : ($hour->hour == 12 ? '12 PM' : ($hour->hour - 12) . ' PM'));
                    $response .= ($index + 1) . ". {$hourLabel}: ₱" . number_format($hour->total_sales, 2) . " ({$hour->transaction_count} transactions)\n";
                }
            }
            
            $response .= "\n💡 **Dashboard-Based Insights:**\n";
            $response .= "• Use dashboard hourly charts for detailed analysis\n";
            $response .= "• Monitor patterns across different date ranges\n";
            $response .= "• Compare hourly performance by store\n";
            $response .= "• Plan staffing based on transaction volume";
            
            return $response;
        }
        
        // WASTE ANALYSIS - Dashboard Aligned
        if (str_contains($message, 'waste') || str_contains($message, 'loss') || str_contains($message, 'damage')) {
            $wasteData = collect($salesContext['waste_analysis'])->take(10);
            $response = "🗑️ **Waste Analysis (Dashboard Data)**\n\n";
            
            if ($wasteData->isEmpty()) {
                $response .= "No waste data available for the current period.\n\n";
                $response .= "💡 **Note**: Waste data comes from stock counting transactions.\n";
                $response .= "• Check if waste entries are being recorded properly\n";
                $response .= "• Review stockcountingtrans table for WASTETYPE entries\n";
            } else {
                $totalWasteQuantity = $wasteData->sum('total_waste_quantity');
                $uniqueItems = $wasteData->count();
                
                $response .= "📊 **Waste Summary:**\n";
                $response .= "• Total Waste Quantity: " . number_format($totalWasteQuantity) . " units\n";
                $response .= "• Unique Items with Waste: {$uniqueItems}\n";
                $response .= "• Store Coverage: " . $wasteData->max('store_count') . " stores affected\n\n";
                
                $response .= "🔝 **Top Waste Items:**\n";
                foreach ($wasteData as $index => $item) {
                    $response .= ($index + 1) . ". **{$item->itemname}** ({$item->waste_type})\n";
                    $response .= "   • Waste Quantity: " . number_format($item->total_waste_quantity) . " units\n";
                    $response .= "   • Stores Affected: {$item->store_count}\n\n";
                }
                
                // Group by waste type
                $wasteByType = $wasteData->groupBy('waste_type');
                $response .= "📋 **Waste by Type:**\n";
                foreach ($wasteByType as $type => $items) {
                    $typeQuantity = $items->sum('total_waste_quantity');
                    $response .= "• {$type}: " . number_format($typeQuantity) . " units (" . $items->count() . " items)\n";
                }
            }
            
            $response .= "\n💡 **Dashboard Waste Insights:**\n";
            $response .= "• Monitor waste trends through dashboard charts\n";
            $response .= "• Focus on high-quantity waste items for reduction\n";
            $response .= "• Analyze waste patterns by store and time period\n";
            $response .= "• Use data for inventory and ordering optimization";
            
            return $response;
        }
        
        // FINANCIAL METRICS - Dashboard Aligned  
        if (str_contains($message, 'financial') || str_contains($message, 'revenue') || str_contains($message, 'profit') || str_contains($message, 'vat') || str_contains($message, 'cost')) {
            $response = "💰 **Financial Metrics (Dashboard Data)**\n\n";
            
            $grossSales = $dashboardMetrics['totalGross'] ?? 0;
            $netSales = $dashboardMetrics['totalNetsales'] ?? 0;
            $totalDiscount = $dashboardMetrics['totalDiscount'] ?? 0;
            $totalCost = $dashboardMetrics['totalCost'] ?? 0;
            $totalVat = $dashboardMetrics['totalVat'] ?? 0;
            $vatableSales = $dashboardMetrics['totalVatableSales'] ?? 0;
            
            $response .= "📊 **Revenue Metrics:**\n";
            $response .= "• Gross Sales: ₱" . number_format($grossSales, 2) . "\n";
            $response .= "• Net Sales: ₱" . number_format($netSales, 2) . "\n";
            $response .= "• Total Discounts: ₱" . number_format($totalDiscount, 2) . "\n";
            $response .= "• Gross Margin: ₱" . number_format($grossSales - $totalCost, 2) . "\n\n";
            
            $response .= "🧾 **Tax & Cost Analysis:**\n";
            $response .= "• VAT Amount: ₱" . number_format($totalVat, 2) . "\n";
            $response .= "• Vatable Sales: ₱" . number_format($vatableSales, 2) . "\n";
            $response .= "• Total Cost: ₱" . number_format($totalCost, 2) . "\n";
            $response .= "• Cost Ratio: " . number_format($grossSales > 0 ? ($totalCost / $grossSales) * 100 : 0, 1) . "%\n\n";
            
            $response .= "📈 **Performance Ratios:**\n";
            $response .= "• Discount Rate: " . number_format($grossSales > 0 ? ($totalDiscount / $grossSales) * 100 : 0, 1) . "%\n";
            $response .= "• Net Margin: " . number_format($grossSales > 0 ? (($netSales - $totalCost) / $grossSales) * 100 : 0, 1) . "%\n";
            $response .= "• VAT Rate: " . number_format($vatableSales > 0 ? ($totalVat / $vatableSales) * 100 : 0, 1) . "%\n\n";
            
            $response .= "🎯 **Financial Health Indicators:**\n";
            $discountRate = $grossSales > 0 ? ($totalDiscount / $grossSales) * 100 : 0;
            if ($discountRate < 5) {
                $response .= "• ✅ Healthy discount rate - Well controlled\n";
            } elseif ($discountRate < 15) {
                $response .= "• ⚠️ Moderate discounting - Monitor effectiveness\n";
            } else {
                $response .= "• 🔴 High discount rate - Review strategy\n";
            }
            
            $costRatio = $grossSales > 0 ? ($totalCost / $grossSales) * 100 : 0;
            if ($costRatio < 60) {
                $response .= "• ✅ Good cost control - Healthy margins\n";
            } elseif ($costRatio < 80) {
                $response .= "• ⚠️ Monitor costs - Margin pressure\n";
            } else {
                $response .= "• 🔴 High cost ratio - Review pricing strategy\n";
            }
            
            return $response;
        }
        
        // MONTHLY COMPARISON AND TRENDS
        if (str_contains($message, 'month') || str_contains($message, 'trend') || str_contains($message, 'comparison') || str_contains($message, 'growth')) {
            $weeklyData = collect($salesContext['weekly_data']);
            $avgDailySales = $weeklyData->avg('daily_sales');
            
            $response = "📈 **Monthly Performance & Trends**\n\n";
            $response .= "💰 **Monthly Comparison:**\n";
            $response .= "• This Month: ₱" . number_format($thisMonthSales, 2) . "\n";
            $response .= "• Last Month: ₱" . number_format($lastMonthSales, 2) . "\n";
            $response .= "• Monthly Change: " . ($monthlyChange >= 0 ? '+' : '') . number_format($monthlyChange, 1) . "%\n\n";
            
            $response .= "📊 **Weekly Trend (Last 7 Days):**\n";
            foreach ($weeklyData->sortByDesc('sale_date') as $day) {
                $dayName = Carbon::parse($day->sale_date)->format('l');
                $response .= "• " . $dayName . " (" . $day->sale_date . "): ₱" . number_format($day->daily_sales, 2) . 
                           " (" . $day->daily_transactions . " transactions)\n";
            }
            
            $response .= "\n🎯 **Trend Analysis:**\n";
            if ($monthlyChange > 10) {
                $response .= "• **Excellent Growth** - Strong upward trend\n";
                $response .= "• Continue current strategies and expand successful initiatives\n";
            } elseif ($monthlyChange > 0) {
                $response .= "• **Positive Growth** - Steady improvement\n";
                $response .= "• Identify growth drivers and amplify them\n";
            } else {
                $response .= "• **Opportunity for Improvement** - Consider new strategies\n";
                $response .= "• Review successful past campaigns and market conditions\n";
            }
            
            $response .= "• 7-Day Average: ₱" . number_format($avgDailySales, 2) . " per day";
            
            return $response;
        }
        
        // IMPROVEMENT STRATEGIES
        if (str_contains($message, 'strategy') || str_contains($message, 'suggest') || str_contains($message, 'improve') || str_contains($message, 'increase') || str_contains($message, 'help')) {
            $topItems = collect($salesContext['top_items'])->take(3);
            $lowItems = collect($salesContext['low_performing_items'])->take(3);
            
            $response = "💡 **Data-Driven Sales Improvement Strategies**\n\n";
            $response .= "🎯 **Immediate Actions Based on Your Data:**\n\n";
            
            $response .= "**1. Inventory Optimization**\n";
            $response .= "• **Stock up on winners**: " . $topItems->pluck('itemname')->implode(', ') . "\n";
            $response .= "• **Review slow movers**: " . $lowItems->pluck('itemname')->implode(', ') . "\n";
            $response .= "• Consider promotions or repositioning for slow items\n\n";
            
            $response .= "**2. Store Performance Enhancement**\n";
            $topStore = collect($salesContext['top_stores'])->first();
            if ($topStore) {
                $response .= "• **Replicate success**: Study what " . $topStore->store . " does right\n";
                $response .= "• **Best practices**: Average transaction of ₱" . number_format($topStore->avg_transaction, 2) . "\n";
            }
            $response .= "• Train underperforming stores on successful strategies\n\n";
            
            $response .= "**3. Customer Experience**\n";
            $response .= "• **Payment optimization**: Promote digital payments (GCash, Cards)\n";
            $response .= "• **Peak hour efficiency**: Staff adequately during busy periods\n";
            $response .= "• **Average transaction target**: Aim for ₱" . number_format($avgTransactionValue * 1.1, 2) . " (+10%)\n\n";
            
            $response .= "**4. Revenue Growth**\n";
            if ($dailyChange >= 0) {
                $response .= "• **Maintain momentum**: Current daily trend is positive (+" . number_format($dailyChange, 1) . "%)\n";
            } else {
                $response .= "• **Recovery focus**: Address " . number_format(abs($dailyChange), 1) . "% daily decline\n";
            }
            $response .= "• **Upselling opportunities**: Focus on high-margin items\n";
            $response .= "• **Cross-selling**: Bundle popular items with slow movers\n\n";
            
            $response .= "🚀 **Implementation Priority**: Start with inventory optimization and staff training for immediate impact.";
            
            return $response;
        }
        
        // FINANCIAL SUMMARY AND INSIGHTS
        if (str_contains($message, 'revenue') || str_contains($message, 'profit') || str_contains($message, 'financial') || str_contains($message, 'money')) {
            $totalItems = $salesContext['today']->total_quantity ?? 0;
            $totalDiscounts = $salesContext['today']->total_discount ?? 0;
            $discountRate = $todayGross > 0 ? ($totalDiscounts / $todayGross) * 100 : 0;
            
            $response = "💰 **Financial Performance Summary**\n\n";
            $response .= "📊 **Today's Financial Metrics:**\n";
            $response .= "• **Gross Revenue**: ₱" . number_format($todayGross, 2) . "\n";
            $response .= "• **Net Revenue**: ₱" . number_format($salesContext['today']->total_net ?? 0, 2) . "\n";
            $response .= "• **Total Discounts**: ₱" . number_format($totalDiscounts, 2) . "\n";
            $response .= "• **Discount Rate**: " . number_format($discountRate, 1) . "%\n";
            $response .= "• **Revenue per Item**: ₱" . number_format($totalItems > 0 ? $todayGross / $totalItems : 0, 2) . "\n\n";
            
            $response .= "📈 **Revenue Trends:**\n";
            $response .= "• **Daily Change**: " . ($dailyChange >= 0 ? '+' : '') . number_format($dailyChange, 1) . "% vs yesterday\n";
            $response .= "• **Monthly Progress**: " . ($monthlyChange >= 0 ? '+' : '') . number_format($monthlyChange, 1) . "% vs last month\n\n";
            
            $response .= "💡 **Financial Health Indicators:**\n";
            if ($discountRate < 5) {
                $response .= "• ✅ **Healthy discount rate** - Well controlled promotions\n";
            } elseif ($discountRate < 15) {
                $response .= "• ⚠️ **Moderate discounting** - Monitor promotional effectiveness\n";
            } else {
                $response .= "• 🔴 **High discount rate** - Review promotional strategy\n";
            }
            
            if ($avgTransactionValue > 100) {
                $response .= "• ✅ **Good average transaction** - Customers buying more per visit\n";
            } else {
                $response .= "• 🎯 **Opportunity**: Increase average transaction through upselling\n";
            }
            
            return $response;
        }
        
        // DEFAULT COMPREHENSIVE DASHBOARD
        return "👋 **Welcome to Your AI Sales Assistant!**\n\n" .
               "📊 **Today's Quick Summary:**\n" .
               "• Sales: ₱" . number_format($todayGross, 2) . " (" . ($dailyChange >= 0 ? '+' : '') . number_format($dailyChange, 1) . "% vs yesterday)\n" .
               "• Transactions: " . number_format($salesContext['today']->transaction_count ?? 0) . "\n" .
               "• Items Sold: " . number_format($salesContext['today']->total_quantity ?? 0) . "\n" .
               "• Active Stores: " . ($salesContext['today']->active_stores ?? 0) . "\n\n" .
               "🔍 **Ask me about:**\n" .
               "• **\"How are sales today?\"** - Detailed performance analysis\n" .
               "• **\"What are our best sellers?\"** - Top products with metrics\n" .
               "• **\"Which stores perform best?\"** - Store comparison\n" .
               "• **\"What are the peak hours?\"** - Hourly sales patterns\n" .
               "• **\"Show me monthly trends\"** - Growth analysis\n" .
               "• **\"How can we improve sales?\"** - Data-driven strategies\n" .
               "• **\"What's our financial status?\"** - Revenue breakdown\n\n" .
               "💡 Just type your question and I'll analyze your ECPOS data to provide accurate insights!";
    }

    // Additional specialized analysis methods for more accurate responses
    protected function getDetailedStoreAnalysis($storeId = null)
    {
        $today = Carbon::today();
        $thisMonth = Carbon::now()->startOfMonth();
        
        $query = DB::table('rbotransactionsalestrans')
            ->whereDate('createddate', '>=', $thisMonth);
            
        if ($storeId) {
            $query->where('store', $storeId);
        }
        
        return $query->selectRaw('
            store,
            SUM(ABS(grossamount)) as total_sales,
            SUM(ABS(netamount)) as total_net,
            SUM(ABS(discamount)) as total_discounts,
            COUNT(DISTINCT transactionid) as transaction_count,
            COUNT(DISTINCT itemid) as unique_items,
            AVG(ABS(grossamount)) as avg_transaction,
            SUM(ABS(qty)) as total_items_sold,
            MAX(createddate) as last_sale,
            MIN(createddate) as first_sale
        ')
        ->groupBy('store')
        ->orderByDesc('total_sales')
        ->get();
    }
    
    protected function getItemPerformanceAnalysis($itemId = null, $days = 30)
    {
        $startDate = Carbon::now()->subDays($days);
        
        $query = DB::table('rbotransactionsalestrans')
            ->whereDate('createddate', '>=', $startDate);
            
        if ($itemId) {
            $query->where('itemid', $itemId);
        }
        
        return $query->selectRaw('
            itemid,
            itemname,
            SUM(ABS(qty)) as total_quantity,
            SUM(ABS(grossamount)) as total_revenue,
            SUM(ABS(netamount)) as total_net,
            SUM(ABS(discamount)) as total_discounts,
            AVG(price) as avg_price,
            COUNT(DISTINCT transactionid) as frequency,
            COUNT(DISTINCT store) as store_coverage,
            STDDEV(ABS(grossamount)) as revenue_volatility
        ')
        ->groupBy('itemid', 'itemname')
        ->orderByDesc('total_quantity')
        ->get();
    }
    
    protected function getSalesVelocityTrends($days = 7)
    {
        $startDate = Carbon::now()->subDays($days);
        
        return DB::table('rbotransactionsalestrans')
            ->whereDate('createddate', '>=', $startDate)
            ->selectRaw('
                DATE(createddate) as sale_date,
                DAYOFWEEK(createddate) as day_of_week,
                HOUR(createddate) as hour,
                SUM(ABS(grossamount)) as hourly_sales,
                COUNT(DISTINCT transactionid) as hourly_transactions,
                AVG(ABS(grossamount)) as avg_transaction_value
            ')
            ->groupBy('sale_date', 'day_of_week', 'hour')
            ->orderBy('sale_date')
            ->orderBy('hour')
            ->get();
    }
    
    protected function getPaymentMethodAnalysis($days = 30)
    {
        $startDate = Carbon::now()->subDays($days);
        
        return DB::table('rbotransactiontables')
            ->whereDate('createddate', '>=', $startDate)
            ->selectRaw('
                SUM(CASE WHEN cash > 0 THEN ABS(cash) ELSE 0 END) as cash_total,
                SUM(CASE WHEN gcash > 0 THEN ABS(gcash) ELSE 0 END) as gcash_total,
                SUM(CASE WHEN card > 0 THEN ABS(card) ELSE 0 END) as card_total,
                SUM(CASE WHEN foodpanda > 0 THEN ABS(foodpanda) ELSE 0 END) as foodpanda_total,
                SUM(CASE WHEN grabfood > 0 THEN ABS(grabfood) ELSE 0 END) as grabfood_total,
                COUNT(CASE WHEN cash > 0 THEN 1 END) as cash_transactions,
                COUNT(CASE WHEN gcash > 0 THEN 1 END) as gcash_transactions,
                COUNT(CASE WHEN card > 0 THEN 1 END) as card_transactions,
                COUNT(CASE WHEN foodpanda > 0 THEN 1 END) as foodpanda_transactions,
                COUNT(CASE WHEN grabfood > 0 THEN 1 END) as grabfood_transactions,
                SUM(ABS(grossamount)) as total_revenue
            ')
            ->first();
    }
    
    protected function getCategoryPerformance()
    {
        $thisMonth = Carbon::now()->startOfMonth();
        
        return DB::table('rbotransactionsalestrans')
            ->whereDate('createddate', '>=', $thisMonth)
            ->selectRaw('
                itemgroup,
                COUNT(DISTINCT itemid) as unique_items,
                SUM(ABS(qty)) as total_quantity,
                SUM(ABS(grossamount)) as total_revenue,
                AVG(price) as avg_price,
                COUNT(DISTINCT transactionid) as transaction_frequency
            ')
            ->groupBy('itemgroup')
            ->orderByDesc('total_revenue')
            ->get();
    }

    public function getSampleQuestions()
    {
        return [
            "How are our sales performing today compared to yesterday?",
            "What are our top-selling items this month?",
            "Which stores are performing the best?",
            "Can you suggest strategies to increase our daily sales?",
            "What trends do you see in our sales data?",
            "What are our peak sales hours today?",
            "Show me this month vs last month comparison",
            "What's our financial performance summary?",
            "How can we improve our sales strategies?",
            "Which payment methods are most popular?",
            "What items need more promotion?",
            "How can we optimize our inventory?"
        ];
    }
}