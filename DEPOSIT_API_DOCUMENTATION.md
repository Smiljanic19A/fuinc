# 💰 Deposit API Documentation

## Overview
The Deposit API provides endpoints for managing cryptocurrency deposit requests with user relationships. Users can request deposits, admins can provide wallet addresses, and both can track deposit statuses with full user information for dashboard display.

## Base URL
All endpoints are prefixed with `/v1/deposits/`

---

## 1. Request Deposit
**POST** `/v1/deposits/request`

Creates a new deposit request for a user.

### Request Body
```json
{
    "user_id": 123,
    "amount": 0.5,
    "currency": "BTC",
    "network": "Bitcoin"
}
```

### Request Fields
| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `user_id` | integer | ✅ | User ID (must exist in users table) |
| `amount` | number | ✅ | Deposit amount (minimum: 0.00000001) |
| `currency` | string | ✅ | Currency code (BTC, ETH, USDT, USDC, BNB, ADA, XRP, SOL, DOT, MATIC) |
| `network` | string | ✅ | Blockchain network (max 50 chars) |

### Success Response (201)
```json
{
    "success": true,
    "message": "Deposit request created successfully",
    "data": {
        "id": 1,
        "user_id": 123,
        "amount": "0.50000000",
        "currency": "BTC",
        "network": "Bitcoin",
        "address": null,
        "filled": false,
        "created_at": "2025-06-16T21:30:00Z",
        "updated_at": "2025-06-16T21:30:00Z",
        "user": {
            "id": 123,
            "name": "John Doe",
            "email": "john@example.com"
        }
    }
}
```

### Error Responses
**422 Validation Error:**
```json
{
    "success": false,
    "message": "Validation failed",
    "errors": {
        "user_id": ["The selected user id is invalid."],
        "amount": ["The amount field is required."],
        "currency": ["The selected currency is invalid."]
    }
}
```

---

## 2. Provide Wallet Address (Admin)
**POST** `/v1/deposits/{id}/address`

Updates a deposit request with a wallet address (typically done by admin for dashboard management).

### URL Parameters
| Parameter | Type | Description |
|-----------|------|-------------|
| `id` | integer | Deposit ID |

### Request Body
```json
{
    "address": "1A1zP1eP5QGefi2DMPTfTL5SLmv7DivfNa"
}
```

### Request Fields
| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `address` | string | ✅ | Wallet address (max 255 chars) |

### Success Response (200)
```json
{
    "success": true,
    "message": "Wallet address provided successfully",
    "data": {
        "id": 1,
        "user_id": 123,
        "amount": "0.50000000",
        "currency": "BTC",
        "network": "Bitcoin",
        "address": "1A1zP1eP5QGefi2DMPTfTL5SLmv7DivfNa",
        "filled": false,
        "created_at": "2025-06-16T21:30:00Z",
        "updated_at": "2025-06-16T21:35:00Z",
        "user": {
            "id": 123,
            "name": "John Doe",
            "email": "john@example.com"
        }
    }
}
```

### Error Responses
**404 Not Found:**
```json
{
    "success": false,
    "message": "Deposit not found"
}
```

**422 Validation Error:**
```json
{
    "success": false,
    "message": "Validation failed",
    "errors": {
        "address": ["The address field is required."]
    }
}
```

---

## 3. Get All Deposits (Admin Dashboard)
**GET** `/v1/deposits/all`

Retrieves all deposits with user information, pagination, and filtering options. Perfect for admin dashboard display.

### Query Parameters
| Parameter | Type | Optional | Description |
|-----------|------|----------|-------------|
| `per_page` | integer | ✅ | Results per page (default: 15) |
| `status` | string | ✅ | Filter by status: `filled` or `pending` |
| `currency` | string | ✅ | Filter by currency (e.g., BTC, ETH) |

### Example Requests
```
GET /v1/deposits/all
GET /v1/deposits/all?status=pending
GET /v1/deposits/all?currency=BTC&per_page=25
GET /v1/deposits/all?status=filled&currency=ETH
```

### Success Response (200)
```json
{
    "success": true,
    "message": "Deposits retrieved successfully",
    "data": {
        "current_page": 1,
        "data": [
            {
                "id": 1,
                "user_id": 123,
                "amount": "0.50000000",
                "currency": "BTC",
                "network": "Bitcoin",
                "address": "1A1zP1eP5QGefi2DMPTfTL5SLmv7DivfNa",
                "filled": false,
                "created_at": "2025-06-16T21:30:00Z",
                "updated_at": "2025-06-16T21:35:00Z",
                "user": {
                    "id": 123,
                    "name": "John Doe",
                    "email": "john@example.com"
                }
            },
            {
                "id": 2,
                "user_id": 456,
                "amount": "100.00000000",
                "currency": "USDT",
                "network": "Ethereum",
                "address": "0x742d35cc6bf8c4c9c1a8cb3c4c9babe8dc8ec8c4",
                "filled": true,
                "created_at": "2025-06-16T20:15:00Z",
                "updated_at": "2025-06-16T21:00:00Z",
                "user": {
                    "id": 456,
                    "name": "Jane Smith",
                    "email": "jane@example.com"
                }
            }
        ],
        "first_page_url": "http://localhost:8000/v1/deposits/all?page=1",
        "from": 1,
        "last_page": 1,
        "last_page_url": "http://localhost:8000/v1/deposits/all?page=1",
        "links": [...],
        "next_page_url": null,
        "path": "http://localhost:8000/v1/deposits/all",
        "per_page": 15,
        "prev_page_url": null,
        "to": 2,
        "total": 2
    }
}
```

---

## 4. Get User Deposits
**GET** `/v1/deposits/user/{userId}`

Retrieves all deposits for a specific user with user information included.

### URL Parameters
| Parameter | Type | Description |
|-----------|------|-------------|
| `userId` | integer | User ID |

### Example Request
```
GET /v1/deposits/user/123
```

### Success Response (200)
```json
{
    "success": true,
    "message": "Deposits for user 123 retrieved successfully",
    "data": [
        {
            "id": 1,
            "user_id": 123,
            "amount": "0.50000000",
            "currency": "BTC",
            "network": "Bitcoin",
            "address": "1A1zP1eP5QGefi2DMPTfTL5SLmv7DivfNa",
            "filled": false,
            "created_at": "2025-06-16T21:30:00Z",
            "updated_at": "2025-06-16T21:35:00Z",
            "user": {
                "id": 123,
                "name": "John Doe",
                "email": "john@example.com"
            }
        },
        {
            "id": 3,
            "user_id": 123,
            "amount": "50.00000000",
            "currency": "USDT",
            "network": "BSC",
            "address": null,
            "filled": false,
            "created_at": "2025-06-16T22:00:00Z",
            "updated_at": "2025-06-16T22:00:00Z",
            "user": {
                "id": 123,
                "name": "John Doe",
                "email": "john@example.com"
            }
        }
    ]
}
```

---

## Database Schema

### Deposits Table
```sql
CREATE TABLE deposits (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    user_id BIGINT NOT NULL,
    amount DECIMAL(20,8) NOT NULL,
    currency VARCHAR(10) NOT NULL,
    network VARCHAR(50) NOT NULL,
    address VARCHAR(255) NULL,
    filled BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_filled (user_id, filled),
    INDEX idx_currency_filled (currency, filled),
    INDEX idx_address (address),
    INDEX idx_created_at (created_at)
);
```

### Eloquent Relationships
```php
// Deposit Model
public function user(): BelongsTo
{
    return $this->belongsTo(User::class);
}

// User Model (add this to your User model)
public function deposits(): HasMany
{
    return $this->hasMany(Deposit::class);
}
```

---

## Deposit Status Flow

1. **Request Created** → `filled: false, address: null, user: {...}`
2. **Address Provided** → `filled: false, address: "wallet_address", user: {...}`
3. **Deposit Completed** → `filled: true, user: {...}` (manually updated)

---

## Admin Dashboard Integration

### Key Features for Dashboard:
- ✅ **User Information**: Every response includes user details (id, name, email)
- ✅ **Status Filtering**: Filter by `pending` or `filled` deposits
- ✅ **Currency Filtering**: Filter by specific cryptocurrencies
- ✅ **Pagination**: Handle large datasets efficiently
- ✅ **Real-time Updates**: Use these endpoints to refresh dashboard data

### Dashboard Data Points:
- User name and email for customer service
- Deposit amount and currency for financial tracking
- Network information for blockchain verification
- Address assignment status for operational workflow
- Creation and update timestamps for audit trails

---

## Common Error Responses

### 500 Server Error
```json
{
    "success": false,
    "message": "Failed to create deposit request",
    "error": "Database connection failed"
}
```

### 422 Validation Error
```json
{
    "success": false,
    "message": "Validation failed",
    "errors": {
        "field_name": ["Error message"]
    }
}
```

### 404 Not Found
```json
{
    "success": false,
    "message": "Deposit not found"
}
```

---

## Supported Currencies
- **BTC** (Bitcoin)
- **ETH** (Ethereum)
- **USDT** (Tether)
- **USDC** (USD Coin)
- **BNB** (Binance Coin)
- **ADA** (Cardano)
- **XRP** (Ripple)
- **SOL** (Solana)
- **DOT** (Polkadot)
- **MATIC** (Polygon)

---

## Usage Examples

### Complete Admin Workflow
```javascript
// 1. View all pending deposits for dashboard
const pendingDeposits = await fetch('/v1/deposits/all?status=pending');

// 2. Provide wallet address for specific deposit
const addressUpdate = await fetch(`/v1/deposits/${depositId}/address`, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({
        address: '1A1zP1eP5QGefi2DMPTfTL5SLmv7DivfNa'
    })
});

// 3. Check specific user's deposit history
const userDeposits = await fetch('/v1/deposits/user/123');

// 4. Filter by currency for reporting
const btcDeposits = await fetch('/v1/deposits/all?currency=BTC');
```

### User Flow
```javascript
// 1. User requests deposit
const depositRequest = await fetch('/v1/deposits/request', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({
        user_id: 123,
        amount: 0.5,
        currency: 'BTC',
        network: 'Bitcoin'
    })
});

// 2. User checks their deposit history
const myDeposits = await fetch('/v1/deposits/user/123');
```

---

## Migration Required

Before using these endpoints, run the migration to add user_id to deposits:

```bash
php artisan migrate
```

This API provides a complete deposit management system with user relationships, perfect for admin dashboards and user account management! 