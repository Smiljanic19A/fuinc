<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\{
    UserManagementController,
    MarketController,
    CoinController,
    OrderController,
    PositionController,
    ChartController,
    WalletController,
    AdminWalletController,
    AnnouncementController,
    PromiseController,
    SuperAdminUserController,
    SuperAdminTradeController,
    SuperAdminPositionController
};

Route::get('/', function () {
    return view('welcome');
});

// API v1 prefix
Route::prefix('v1')->group(function () {
    
    // User Management API Routes
    Route::prefix('users')->group(function () {
        // Public routes (no authentication required)
        Route::post('/create', [UserManagementController::class, 'create']);
        Route::get('/fetch', [UserManagementController::class, 'fetch']);
        Route::post('/authenticate', [UserManagementController::class, 'authenticate']);
        Route::post('/login', [UserManagementController::class, 'login']);
        
        // Protected routes (superadmin only)
        Route::middleware(['auth', 'superadmin'])->group(function () {
            Route::post('/promote', [UserManagementController::class, 'promoteUser']);
            Route::post('/demote', [UserManagementController::class, 'demoteUser']);
        });
    });

    // 🪙 Trading / Positions
    Route::prefix('markets')->group(function () {
        Route::get('/', [MarketController::class, 'index']);
        Route::get('/stats', [MarketController::class, 'stats']);
        Route::get('/trending', [MarketController::class, 'trending']);
        Route::get('/{symbol}', [MarketController::class, 'show']);
    });

    Route::prefix('orders')->middleware(['auth'])->group(function () {
        Route::post('/', [OrderController::class, 'create']);
        Route::get('/history', [OrderController::class, 'history']);
        Route::get('/open', [OrderController::class, 'open']);
        Route::delete('/{id}', [OrderController::class, 'cancel']);
    });

    Route::prefix('positions')->middleware(['auth'])->group(function () {
        Route::get('/', [PositionController::class, 'index']);
        Route::get('/{id}', [PositionController::class, 'show']);
        Route::post('/close/{id}', [PositionController::class, 'close']);
    });

    // 📈 Charts & Market Data
    Route::prefix('charts')->group(function () {
        Route::get('/{symbol}/candles', [ChartController::class, 'candles']);
        Route::get('/{symbol}/ticker', [ChartController::class, 'ticker']);
        Route::get('/{symbol}/depth', [ChartController::class, 'orderBook']);
        Route::get('/{symbol}/trades', [ChartController::class, 'recentTrades']);
    });

    // 💰 Deposit & Withdrawals (Fake Wallets)
    Route::prefix('wallets')->middleware(['auth'])->group(function () {
        Route::get('/balances', [WalletController::class, 'balances']);
        Route::post('/deposit/request', [WalletController::class, 'requestDeposit']);
        Route::get('/deposit/history', [WalletController::class, 'depositHistory']);
        Route::post('/withdraw/request', [WalletController::class, 'requestWithdrawal']);
        Route::get('/withdraw/history', [WalletController::class, 'withdrawalHistory']);
        Route::get('/transactions', [WalletController::class, 'transactionHistory']);
    });

    // 🔥 Hot Coins & Coins
    Route::prefix('coins')->group(function () {
        Route::get('/', [CoinController::class, 'index']);
        Route::get('/hot', [CoinController::class, 'hot']);
        Route::get('/new', [CoinController::class, 'new']);
        Route::get('/trending', [CoinController::class, 'trending']);
        Route::get('/categories', [CoinController::class, 'categories']);
        Route::get('/{identifier}', [CoinController::class, 'show']);
    });

    // 📣 Announcements
    Route::prefix('announcements')->group(function () {
        Route::get('/', [AnnouncementController::class, 'index']);
        Route::get('/{id}', [AnnouncementController::class, 'show']);
        Route::post('/{id}/view', [AnnouncementController::class, 'markAsViewed'])->middleware(['auth']);
    });

    // 📄 Promises / Bonuses
    Route::prefix('promises')->middleware(['auth'])->group(function () {
        Route::get('/', [PromiseController::class, 'index']);
        Route::get('/{id}', [PromiseController::class, 'show']);
        Route::post('/{id}/redeem', [PromiseController::class, 'redeem']);
    });

    // 🛠 SuperAdmin Controls
    Route::prefix('admin')->middleware(['auth', 'superadmin'])->group(function () {
        
        // Admin Wallet Management
        Route::prefix('wallets')->group(function () {
            Route::get('/deposits', [AdminWalletController::class, 'deposits']);
            Route::post('/deposits/{id}/address', [AdminWalletController::class, 'provideDepositAddress']);
            Route::post('/deposits/{id}/approve', [AdminWalletController::class, 'approveDeposit']);
            Route::post('/deposits/{id}/reject', [AdminWalletController::class, 'rejectDeposit']);
            
            Route::get('/withdrawals', [AdminWalletController::class, 'withdrawals']);
            Route::post('/withdrawals/{id}/complete', [AdminWalletController::class, 'completeWithdrawal']);
            Route::post('/withdrawals/{id}/reject', [AdminWalletController::class, 'rejectWithdrawal']);
        });

        // User Management
        Route::prefix('users')->group(function () {
            Route::get('/', [SuperAdminUserController::class, 'index']);
            Route::get('/{id}', [SuperAdminUserController::class, 'show']);
            Route::post('/{id}/fund', [SuperAdminUserController::class, 'fundAccount']);
            Route::post('/{id}/promises', [SuperAdminUserController::class, 'addPromise']);
            Route::delete('/{id}/clear', [SuperAdminUserController::class, 'clearUserData']);
        });

        // Manual Trading
        Route::prefix('trades')->group(function () {
            Route::post('/create', [SuperAdminTradeController::class, 'createManualTrade']);
            Route::get('/user/{userId}', [SuperAdminTradeController::class, 'getUserTrades']);
            Route::put('/{id}', [SuperAdminTradeController::class, 'updateTrade']);
            Route::delete('/{id}', [SuperAdminTradeController::class, 'deleteTrade']);
        });

        // Manual Positions
        Route::prefix('positions')->group(function () {
            Route::post('/create', [SuperAdminPositionController::class, 'createManualPosition']);
            Route::get('/user/{userId}', [SuperAdminPositionController::class, 'getUserPositions']);
            Route::put('/{id}', [SuperAdminPositionController::class, 'updatePosition']);
            Route::post('/{id}/close', [SuperAdminPositionController::class, 'closePosition']);
            Route::delete('/{id}', [SuperAdminPositionController::class, 'deletePosition']);
        });

        // Coin Management
        Route::prefix('coins')->group(function () {
            Route::post('/hot', [CoinController::class, 'updateHotStatus']);
            Route::post('/bulk-hot', [CoinController::class, 'bulkUpdateHot']);
        });

        // Announcements Management
        Route::prefix('announcements')->group(function () {
            Route::post('/', [AnnouncementController::class, 'create']);
            Route::put('/{id}', [AnnouncementController::class, 'update']);
            Route::delete('/{id}', [AnnouncementController::class, 'delete']);
            Route::post('/{id}/publish', [AnnouncementController::class, 'publish']);
            Route::post('/{id}/unpublish', [AnnouncementController::class, 'unpublish']);
        });

        // Promise Management
        Route::prefix('promises')->group(function () {
            Route::get('/all', [PromiseController::class, 'adminIndex']);
            Route::post('/', [PromiseController::class, 'adminCreate']);
            Route::put('/{id}', [PromiseController::class, 'adminUpdate']);
            Route::delete('/{id}', [PromiseController::class, 'adminDelete']);
        });
    });
});

// Legacy user management routes (keeping for backward compatibility)
Route::prefix('users')->group(function () {
    // Public routes (no authentication required)
    Route::post('/create', [UserManagementController::class, 'create']);
    Route::get('/fetch', [UserManagementController::class, 'fetch']);
    Route::post('/authenticate', [UserManagementController::class, 'authenticate']);
    
    // Protected routes (superadmin only)
    Route::middleware(['auth', 'superadmin'])->group(function () {
        Route::post('/promote', [UserManagementController::class, 'promoteUser']);
        Route::post('/demote', [UserManagementController::class, 'demoteUser']);
    });
});
    