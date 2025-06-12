# Trading System API Documentation

## Overview

This document provides comprehensive documentation for the FuInc Trading System API. The API follows RESTful principles and returns JSON responses. All endpoints are prefixed with `/api/v1` unless otherwise specified.

## Base URL
```
Production: https://fuinc-main-beylhr.laravel.cloud/api/v1
Development: http://localhost/api/v1
```

## Authentication

The API uses Laravel's built-in authentication system with plain text passwords for development. SuperAdmin routes require additional authorization.

### Authentication Headers
```http
Authorization: Bearer {token}
Content-Type: application/json
```

## Response Format

All API responses follow a consistent format:

### Success Response
```json
{
    "success": true,
    "message": "Operation completed successfully",
    "data": { /* Response data */ },
    "meta": { /* Optional metadata */ }
}
```

### Error Response
```json
{
    "success": false,
    "message": "Error description",
    "errors": { /* Validation errors if applicable */ }
}
```

---

# 🪙 Trading / Positions

## Markets

### GET `/markets`
**Purpose:** Retrieve all available trading pairs  
**Authentication:** Not required  
**Rate Limit:** 60/minute

#### Query Parameters
| Parameter | Type | Description |
|-----------|------|-------------|
| `active` | boolean | Filter by active markets only |
| `symbol` | string | Filter by specific symbol (e.g., `BTCUSDT`) |
| `base_currency` | string | Filter by base currency (e.g., `BTC`) |
| `quote_currency` | string | Filter by quote currency (e.g., `USDT`) |

#### Response
```json
{
    "success": true,
    "message": "Markets retrieved successfully",
    "data": [
        {
            "id": 1,
            "symbol": "BTCUSDT",
            "base_currency": "BTC",
            "quote_currency": "USDT",
            "display_name": "Bitcoin/USDT",
            "current_price": "43250.75",
            "price_change_24h": "1250.75",
            "price_change_percentage_24h": "2.98",
            "high_24h": "44120.50",
            "low_24h": "41890.25",
            "volume_24h": "15420000000.00000000",
            "market_cap": "847000000000.00",
            "min_order_amount": "0.00001000",
            "max_order_amount": "1000.00000000",
            "is_active": true,
            "is_trading_enabled": true
        }
    ],
    "count": 1
}
```

### GET `/markets/{symbol}`
**Purpose:** Get specific market details  
**Authentication:** Not required  
**Rate Limit:** 60/minute

#### Path Parameters
| Parameter | Type | Description |
|-----------|------|-------------|
| `symbol` | string | Market symbol (e.g., `BTCUSDT`) |

#### Response
```json
{
    "success": true,
    "message": "Market details retrieved successfully",
    "data": {
        "id": 1,
        "symbol": "BTCUSDT",
        "base_currency": "BTC",
        "quote_currency": "USDT",
        "display_name": "Bitcoin/USDT",
        "current_price": "43250.75",
        "price_change_24h": "1250.75",
        "price_change_percentage_24h": "2.98",
        "high_24h": "44120.50",
        "low_24h": "41890.25",
        "volume_24h": "15420000000.00000000",
        "min_order_amount": "0.00001000",
        "max_order_amount": "1000.00000000",
        "price_precision": 2,
        "quantity_precision": 5,
        "is_active": true,
        "is_trading_enabled": true,
        "base_coin": {
            "symbol": "BTC",
            "name": "Bitcoin",
            "is_hot": true
        },
        "quote_coin": {
            "symbol": "USDT",
            "name": "Tether"
        }
    }
}
```

## Orders

### POST `/orders`
**Purpose:** Place a new buy/sell order  
**Authentication:** Required  
**Rate Limit:** 30/minute

#### Request Body
```json
{
    "market_id": 1,
    "type": "limit",
    "side": "buy",
    "quantity": "0.001",
    "price": "43000.00",
    "stop_price": null,
    "time_in_force": "GTC"
}
```

#### Parameters
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `market_id` | integer | Yes | Market ID to trade |
| `type` | string | Yes | Order type: `market`, `limit`, `stop_loss`, `take_profit` |
| `side` | string | Yes | Order side: `buy`, `sell` |
| `quantity` | decimal | Yes | Order quantity |
| `price` | decimal | No | Price (required for limit orders) |
| `stop_price` | decimal | No | Stop price for stop orders |
| `time_in_force` | string | No | `GTC`, `IOC`, `FOK` (default: `GTC`) |

#### Response
```json
{
    "success": true,
    "message": "Order placed successfully",
    "data": {
        "id": 123,
        "order_id": "uuid-string",
        "market_id": 1,
        "type": "limit",
        "side": "buy",
        "quantity": "0.00100000",
        "price": "43000.00000000",
        "status": "pending",
        "created_at": "2025-06-12T21:45:00.000000Z"
    }
}
```

### GET `/orders/history`
**Purpose:** Get user's order history  
**Authentication:** Required  
**Rate Limit:** 60/minute

#### Query Parameters
| Parameter | Type | Description |
|-----------|------|-------------|
| `market_id` | integer | Filter by market |
| `status` | string | Filter by status: `pending`, `filled`, `cancelled` |
| `side` | string | Filter by side: `buy`, `sell` |
| `limit` | integer | Number of orders to return (max 100) |
| `page` | integer | Page number for pagination |

#### Response
```json
{
    "success": true,
    "message": "Order history retrieved successfully",
    "data": [
        {
            "id": 123,
            "order_id": "uuid-string",
            "market": {
                "symbol": "BTCUSDT",
                "display_name": "Bitcoin/USDT"
            },
            "type": "limit",
            "side": "buy",
            "quantity": "0.00100000",
            "price": "43000.00000000",
            "filled_quantity": "0.00100000",
            "average_price": "43000.00000000",
            "status": "filled",
            "executed_at": "2025-06-12T21:45:30.000000Z",
            "created_at": "2025-06-12T21:45:00.000000Z"
        }
    ],
    "pagination": {
        "current_page": 1,
        "total_pages": 5,
        "total_count": 47
    }
}
```

### GET `/orders/open`
**Purpose:** Get user's open orders  
**Authentication:** Required  
**Rate Limit:** 60/minute

### DELETE `/orders/{id}`
**Purpose:** Cancel an open order  
**Authentication:** Required  
**Rate Limit:** 30/minute

#### Response
```json
{
    "success": true,
    "message": "Order cancelled successfully",
    "data": {
        "id": 123,
        "status": "cancelled",
        "cancelled_at": "2025-06-12T21:50:00.000000Z"
    }
}
```

## Positions

### GET `/positions`
**Purpose:** Get user's current positions  
**Authentication:** Required  
**Rate Limit:** 60/minute

#### Query Parameters
| Parameter | Type | Description |
|-----------|------|-------------|
| `market_id` | integer | Filter by market |
| `status` | string | Filter by status: `open`, `closed` |
| `side` | string | Filter by side: `long`, `short` |

#### Response
```json
{
    "success": true,
    "message": "Positions retrieved successfully",
    "data": [
        {
            "id": 456,
            "position_id": "uuid-string",
            "market": {
                "symbol": "BTCUSDT",
                "display_name": "Bitcoin/USDT"
            },
            "side": "long",
            "entry_price": "42800.00000000",
            "current_price": "43250.75000000",
            "quantity": "0.50000000",
            "leverage": "2.00",
            "margin_used": "10700.00000000",
            "unrealized_pnl": "225.37500000",
            "pnl_percentage": "2.11",
            "status": "open",
            "opened_at": "2025-06-12T20:30:00.000000Z"
        }
    ]
}
```

### GET `/positions/{id}`
**Purpose:** Get detailed position information  
**Authentication:** Required  
**Rate Limit:** 60/minute

### POST `/positions/close/{id}`
**Purpose:** Manually close a position  
**Authentication:** Required  
**Rate Limit:** 10/minute

#### Request Body
```json
{
    "close_price": "43250.75",
    "reason": "Manual close"
}
```

---

# 📈 Charts & Market Data

## Chart Data

### GET `/charts/{symbol}/candles`
**Purpose:** Get OHLCV candle data for charts  
**Authentication:** Not required  
**Rate Limit:** 60/minute

#### Path Parameters
| Parameter | Type | Description |
|-----------|------|-------------|
| `symbol` | string | Market symbol (e.g., `BTCUSDT`) |

#### Query Parameters
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `timeframe` | string | Yes | `1m`, `5m`, `15m`, `1h`, `4h`, `1d`, `1w`, `1M` |
| `start_time` | timestamp | No | Start time for data |
| `end_time` | timestamp | No | End time for data |
| `limit` | integer | No | Number of candles (max 1000, default 500) |

#### Response
```json
{
    "success": true,
    "message": "Candle data retrieved successfully",
    "data": [
        {
            "timestamp": 1702393200,
            "open": "43000.00000000",
            "high": "43150.50000000",
            "low": "42950.25000000",
            "close": "43100.75000000",
            "volume": "125.50000000",
            "quote_volume": "5402567.50000000",
            "trades_count": 342
        }
    ],
    "meta": {
        "symbol": "BTCUSDT",
        "timeframe": "1h",
        "count": 100
    }
}
```

### GET `/charts/{symbol}/ticker`
**Purpose:** Get current ticker data  
**Authentication:** Not required  
**Rate Limit:** 120/minute

#### Response
```json
{
    "success": true,
    "message": "Ticker data retrieved successfully",
    "data": {
        "symbol": "BTCUSDT",
        "price": "43250.75000000",
        "change": "1250.75000000",
        "changePercent": "2.98",
        "high": "44120.50000000",
        "low": "41890.25000000",
        "volume": "15420000000.00000000",
        "quoteVolume": "668500000000.00000000",
        "timestamp": 1702393200
    }
}
```

---

# 💰 Deposit & Withdrawals (Fake Wallets)

## User Wallet Operations

### POST `/wallets/deposit/request`
**Purpose:** Request a deposit  
**Authentication:** Required  
**Rate Limit:** 10/minute

#### Request Body
```json
{
    "currency": "BTC",
    "amount": "0.1",
    "network": "Bitcoin"
}
```

#### Parameters
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `currency` | string | Yes | Currency code (BTC, ETH, USDT, etc.) |
| `amount` | decimal | Yes | Deposit amount |
| `network` | string | Yes | Network type (Bitcoin, ERC20, TRC20, etc.) |

#### Response
```json
{
    "success": true,
    "message": "Deposit request created successfully",
    "data": {
        "id": 789,
        "transaction_id": "uuid-string",
        "currency": "BTC",
        "amount": "0.10000000",
        "fee_amount": "0.00010000",
        "status": "pending",
        "network": "Bitcoin",
        "wallet_address": null,
        "requested_at": "2025-06-12T21:45:00.000000Z"
    }
}
```

### GET `/wallets/deposit/history`
**Purpose:** Get user's deposit history  
**Authentication:** Required  
**Rate Limit:** 60/minute

#### Query Parameters
| Parameter | Type | Description |
|-----------|------|-------------|
| `currency` | string | Filter by currency |
| `status` | string | Filter by status |
| `limit` | integer | Number of records (max 100) |

### POST `/wallets/withdraw/request`
**Purpose:** Request a withdrawal  
**Authentication:** Required  
**Rate Limit:** 5/minute

#### Request Body
```json
{
    "currency": "BTC",
    "amount": "0.05",
    "wallet_address": "1A1zP1eP5QGefi2DMPTfTL5SLmv7DivfNa",
    "network": "Bitcoin"
}
```

### GET `/wallets/withdraw/history`
**Purpose:** Get user's withdrawal history  
**Authentication:** Required  
**Rate Limit:** 60/minute

---

# 🛠 SuperAdmin Controls

All SuperAdmin endpoints require `superadmin` user type and are protected by middleware.

## Admin Wallet Management

### GET `/admin/deposits`
**Purpose:** View all deposit requests  
**Authentication:** SuperAdmin required  
**Rate Limit:** 120/minute

#### Response
```json
{
    "success": true,
    "message": "Deposit requests retrieved successfully",
    "data": [
        {
            "id": 789,
            "user": {
                "id": 5,
                "name": "John Doe",
                "email": "john@example.com"
            },
            "transaction_id": "uuid-string",
            "currency": "BTC",
            "amount": "0.10000000",
            "status": "pending",
            "requested_at": "2025-06-12T21:45:00.000000Z"
        }
    ]
}
```

### POST `/admin/deposits/{id}/address`
**Purpose:** Provide fake wallet address for deposit  
**Authentication:** SuperAdmin required  
**Rate Limit:** 30/minute

#### Request Body
```json
{
    "wallet_address": "1A1zP1eP5QGefi2DMPTfTL5SLmv7DivfNa",
    "admin_notes": "Fake address generated for testing"
}
```

### GET `/admin/withdrawals`
**Purpose:** View all withdrawal requests  
**Authentication:** SuperAdmin required  
**Rate Limit:** 120/minute

### POST `/admin/withdrawals/{id}/complete`
**Purpose:** Mark withdrawal as processed  
**Authentication:** SuperAdmin required  
**Rate Limit:** 30/minute

#### Request Body
```json
{
    "transaction_hash": "fake-tx-hash-12345",
    "admin_notes": "Withdrawal processed successfully"
}
```

## User Management

### GET `/admin/users`
**Purpose:** List all users with trading data  
**Authentication:** SuperAdmin required  
**Rate Limit:** 120/minute

#### Query Parameters
| Parameter | Type | Description |
|-----------|------|-------------|
| `search` | string | Search by name or email |
| `user_type` | string | Filter by user type |
| `has_positions` | boolean | Filter users with open positions |

### GET `/admin/users/{id}`
**Purpose:** View detailed user information  
**Authentication:** SuperAdmin required  
**Rate Limit:** 60/minute

#### Response
```json
{
    "success": true,
    "message": "User details retrieved successfully",
    "data": {
        "id": 5,
        "name": "John Doe",
        "email": "john@example.com",
        "user_type": "user",
        "created_at": "2025-06-10T10:00:00.000000Z",
        "trading_stats": {
            "total_orders": 25,
            "total_volume": "15000.50000000",
            "open_positions": 3,
            "realized_pnl": "250.75000000"
        },
        "wallet_balances": {
            "BTC": "0.05000000",
            "ETH": "2.50000000",
            "USDT": "1000.00000000"
        }
    }
}
```

### POST `/admin/users/{id}/fund`
**Purpose:** Add/remove fake funds from user account  
**Authentication:** SuperAdmin required  
**Rate Limit:** 10/minute

#### Request Body
```json
{
    "currency": "USDT",
    "amount": "1000.00",
    "type": "add",
    "notes": "Promotional bonus"
}
```

#### Parameters
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `currency` | string | Yes | Currency to modify |
| `amount` | decimal | Yes | Amount to add/remove |
| `type` | string | Yes | `add` or `remove` |
| `notes` | string | No | Admin notes |

### POST `/admin/users/{id}/trades`
**Purpose:** Create/edit manual trades for user  
**Authentication:** SuperAdmin required  
**Rate Limit:** 10/minute

#### Request Body
```json
{
    "market_id": 1,
    "type": "market",
    "side": "buy",
    "quantity": "0.001",
    "price": "43000.00",
    "status": "filled",
    "executed_at": "2025-06-12T21:45:00.000000Z"
}
```

### POST `/admin/users/{id}/positions`
**Purpose:** Create/edit positions manually  
**Authentication:** SuperAdmin required  
**Rate Limit:** 10/minute

#### Request Body
```json
{
    "market_id": 1,
    "side": "long",
    "entry_price": "42800.00",
    "quantity": "0.5",
    "leverage": "2.00",
    "status": "open"
}
```

### POST `/admin/users/{id}/coin-promos`
**Purpose:** Set coins as hot for specific user display  
**Authentication:** SuperAdmin required  
**Rate Limit:** 30/minute

#### Request Body
```json
{
    "coin_ids": [1, 2, 3],
    "promotion_type": "hot",
    "expires_at": "2025-06-20T00:00:00.000000Z"
}
```

### POST `/admin/users/{id}/promises`
**Purpose:** Add/edit promises (fake credit, bonuses)  
**Authentication:** SuperAdmin required  
**Rate Limit:** 10/minute

#### Request Body
```json
{
    "type": "bonus",
    "title": "Special Promotion",
    "description": "Limited time bonus for active trading",
    "amount": "500.00",
    "currency": "USDT",
    "validity_days": 30,
    "minimum_trades": 10
}
```

### DELETE `/admin/users/{id}/clear`
**Purpose:** Reset user account (positions/trades/etc)  
**Authentication:** SuperAdmin required  
**Rate Limit:** 3/minute

#### Request Body
```json
{
    "clear_orders": true,
    "clear_positions": true,
    "clear_wallet_transactions": true,
    "clear_promises": false,
    "confirmation": "CLEAR_USER_DATA"
}
```

---

# 📣 Announcements

### GET `/announcements`
**Purpose:** Get all active announcements  
**Authentication:** Not required  
**Rate Limit:** 60/minute

#### Query Parameters
| Parameter | Type | Description |
|-----------|------|-------------|
| `audience` | string | Filter by audience: `all`, `users`, `superadmins` |
| `homepage_only` | boolean | Get only homepage announcements |

#### Response
```json
{
    "success": true,
    "message": "Announcements retrieved successfully",
    "data": [
        {
            "id": 1,
            "title": "Welcome to FuInc Trading Platform!",
            "content": "Start your cryptocurrency trading journey with us.",
            "type": "success",
            "priority": "high",
            "is_sticky": true,
            "action_url": "/trading",
            "action_text": "Start Trading",
            "published_at": "2025-06-12T00:00:00.000000Z"
        }
    ]
}
```

### POST `/admin/announcements`
**Purpose:** Create new announcement  
**Authentication:** SuperAdmin required  
**Rate Limit:** 10/minute

#### Request Body
```json
{
    "title": "System Maintenance Notice",
    "content": "Scheduled maintenance on Sunday 2-4 AM UTC.",
    "type": "warning",
    "priority": "high",
    "show_on_homepage": true,
    "show_in_dashboard": true,
    "target_audience": "all",
    "expires_at": "2025-06-20T00:00:00.000000Z"
}
```

### DELETE `/admin/announcements/{id}`
**Purpose:** Delete announcement  
**Authentication:** SuperAdmin required  
**Rate Limit:** 30/minute

---

# 🔥 Hot Coins & Coins

### GET `/coins`
**Purpose:** Get all available coins  
**Authentication:** Not required  
**Rate Limit:** 60/minute

#### Query Parameters
| Parameter | Type | Description |
|-----------|------|-------------|
| `hot_only` | boolean | Get only hot coins |
| `new_only` | boolean | Get only new coins |
| `trending_only` | boolean | Get only trending coins |
| `category` | string | Filter by category |

### GET `/coins/hot`
**Purpose:** Get current hot coins (is_hot = 1)  
**Authentication:** Not required  
**Rate Limit:** 120/minute

#### Response
```json
{
    "success": true,
    "message": "Hot coins retrieved successfully",
    "data": [
        {
            "id": 1,
            "symbol": "BTC",
            "name": "Bitcoin",
            "current_price": "43250.75000000",
            "price_change_percentage_24h": "2.98",
            "market_cap": "847000000000.00",
            "volume_24h": "15420000000.00000000",
            "is_hot": true,
            "category": "Currency",
            "icon_url": "/images/coins/btc.png"
        }
    ]
}
```

### POST `/admin/coins/hot`
**Purpose:** Update hot coin status  
**Authentication:** SuperAdmin required  
**Rate Limit:** 30/minute

#### Request Body
```json
{
    "coin_id": 1,
    "is_hot": true
}
```

---

# 📄 Promises / Bonuses

### GET `/user/promises`
**Purpose:** Get user's promises/bonuses  
**Authentication:** Required  
**Rate Limit:** 60/minute

#### Query Parameters
| Parameter | Type | Description |
|-----------|------|-------------|
| `status` | string | Filter by status: `active`, `redeemed`, `expired` |
| `type` | string | Filter by type: `bonus`, `referral`, etc. |

#### Response
```json
{
    "success": true,
    "message": "User promises retrieved successfully",
    "data": [
        {
            "id": 10,
            "promise_id": "uuid-string",
            "type": "bonus",
            "title": "Welcome Bonus",
            "description": "Welcome to FuInc! Enjoy your welcome bonus.",
            "amount": "100.00000000",
            "currency": "USDT",
            "remaining_amount": "75.00000000",
            "status": "active",
            "expires_at": "2025-07-12T00:00:00.000000Z",
            "activated_at": "2025-06-12T00:00:00.000000Z"
        }
    ],
    "summary": {
        "total_amount": "150.00000000",
        "total_remaining": "125.00000000",
        "active_count": 2
    }
}
```

### POST `/admin/users/{id}/promises`
**Purpose:** Add promises to user account  
**Authentication:** SuperAdmin required  
**Rate Limit:** 10/minute

---

# Error Codes

| Code | Description |
|------|-------------|
| 200 | Success |
| 400 | Bad Request - Invalid input |
| 401 | Unauthorized - Authentication required |
| 403 | Forbidden - Insufficient permissions |
| 404 | Not Found - Resource doesn't exist |
| 422 | Validation Error - Input validation failed |
| 429 | Too Many Requests - Rate limit exceeded |
| 500 | Internal Server Error |

# Rate Limiting

The API implements rate limiting to prevent abuse:

- **Standard endpoints:** 60 requests/minute
- **Trading endpoints:** 30 requests/minute  
- **Admin endpoints:** 120 requests/minute
- **Critical operations:** 10 requests/minute

Rate limit headers are included in responses:
```
X-RateLimit-Limit: 60
X-RateLimit-Remaining: 45
X-RateLimit-Reset: 1702393200
```

# Pagination

Endpoints that return lists support pagination:

#### Query Parameters
| Parameter | Type | Description |
|-----------|------|-------------|
| `page` | integer | Page number (default: 1) |
| `limit` | integer | Items per page (max: 100, default: 20) |

#### Response Format
```json
{
    "success": true,
    "data": [...],
    "pagination": {
        "current_page": 1,
        "total_pages": 5,
        "total_count": 97,
        "per_page": 20,
        "has_next_page": true,
        "has_prev_page": false
    }
}
```

---

**Last Updated:** December 12, 2025  
**API Version:** v1.0.0 