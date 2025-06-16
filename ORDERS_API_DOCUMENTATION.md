# 📋 Orders API Documentation

## Overview
The Orders API provides comprehensive order management functionality including viewing orders by user, rejecting orders, marking orders as filled, and retrieving all orders with filtering capabilities. All endpoints return user and market information for admin dashboard integration.

## Base URL
All endpoints are prefixed with `/v1/orders/`

---

## 1. Get User Orders
**GET** `/v1/orders/user/{userId}`

Retrieves all orders for a specific user with complete order, user, and market information.

### URL Parameters
| Parameter | Type | Description |
|-----------|------|-------------|
| `userId` | integer | User ID |

### Example Request
```
GET /v1/orders/user/123
```

### Success Response (200)
```json
{
    "success": true,
    "message": "Orders for user 123 retrieved successfully",
    "data": [
        {
            "id": 1,
            "user_id": 123,
            "market_id": 1,
            "order_id": "uuid-order-123",
            "type": "limit",
            "side": "buy",
            "quantity": "0.10000000",
            "price": "45000.00000000",
            "filled_quantity": "0.00000000",
            "remaining_quantity": "0.10000000",
            "average_price": "0.00000000",
            "total_value": "4500.00000000",
            "fee_amount": "0.00000000",
            "fee_currency": "USDT",
            "status": "pending",
            "stop_price": null,
            "trigger_price": null,
            "time_in_force": "GTC",
            "is_admin_created": false,
            "executed_at": null,
            "cancelled_at": null,
            "cancel_reason": null,
            "metadata": null,
            "created_at": "2025-06-16T21:30:00Z",
            "updated_at": "2025-06-16T21:30:00Z",
            "user": {
                "id": 123,
                "name": "John Doe",
                "email": "john@example.com"
            },
            "market": {
                "id": 1,
                "symbol": "BTC/USDT",
                "display_name": "Bitcoin / Tether",
                "current_price": "46000.00000000"
            }
        }
    ]
}
```

### Error Responses
**404 User Not Found:**
```json
{
    "success": false,
    "message": "User not found"
}
```

---

## 2. Edit Order (Admin)
**PUT** `/v1/admin/orders/{id}/edit`

Edits an existing order and automatically adjusts fund allocations. Only works on open orders.

### URL Parameters
| Parameter | Type | Description |
|-----------|------|-------------|
| `id` | integer | Order ID |

### Request Body (Optional Fields)
```json
{
    "quantity": 0.75,
    "price": 46000.00,
    "stop_price": 44000.00,
    "time_in_force": "GTC"
}
```

### Request Fields
| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `quantity` | number | ❌ | New order quantity |
| `price` | number | ❌ | New order price |
| `stop_price` | number | ❌ | New stop price (for stop orders) |
| `time_in_force` | string | ❌ | Order duration: `GTC`, `IOC`, `FOK` |

### Example Requests
```bash
# Change quantity and price
PUT /v1/admin/orders/456/edit
Content-Type: application/json

{
    "quantity": 0.75,
    "price": 46000.00
}

# Only change price
PUT /v1/admin/orders/456/edit
Content-Type: application/json

{
    "price": 47000.00
}
```

### Success Response (200)
```json
{
    "success": true,
    "message": "Order updated successfully and funds reallocated",
    "data": {
        "order": {
            "id": 456,
            "user_id": 123,
            "market_id": 1,
            "order_id": "uuid-order-456",
            "type": "limit",
            "side": "buy",
            "quantity": "0.75000000",
            "price": "46000.00000000",
            "filled_quantity": "0.00000000",
            "remaining_quantity": "0.75000000",
            "average_price": "0.00000000",
            "total_value": "34500.00000000",
            "fee_amount": "0.00000000",
            "fee_currency": "USDT",
            "status": "pending",
            "created_at": "2025-06-16T21:45:00Z",
            "updated_at": "2025-06-16T22:15:00Z",
            "user": {
                "id": 123,
                "name": "John Doe",
                "email": "john@example.com"
            },
            "market": {
                "id": 1,
                "symbol": "BTC/USDT",
                "display_name": "Bitcoin / Tether",
                "current_price": "46500.00000000"
            }
        },
        "fund_changes": {
            "currency": "USDT",
            "previous_allocation": 22000.00,
            "new_allocation": 34500.00,
            "difference": 12500.00
        }
    }
}
```

### Error Responses
**400 Bad Request (Order cannot be edited):**
```json
{
    "success": false,
    "message": "Order cannot be edited. Current status: filled"
}
```

**400 Bad Request (Insufficient funds):**
```json
{
    "success": false,
    "message": "Insufficient funds for order modification",
    "details": {
        "additional_required": 5000.00,
        "available": 3000.00,
        "currency": "USDT"
    }
}
```

**422 Validation Error:**
```json
{
    "success": false,
    "message": "Validation failed",
    "errors": {
        "quantity": ["The quantity must be at least 0.00000001."]
    }
}
```

---

## 3. Reject Order
**POST** `/v1/orders/{id}/reject`

Cancels an order and marks it as rejected by admin. Only works on open orders.

### URL Parameters
| Parameter | Type | Description |
|-----------|------|-------------|
| `id` | integer | Order ID |

### Example Request
```
POST /v1/orders/456/reject
```
*No request body required*

### Success Response (200)
```json
{
    "success": true,
    "message": "Order rejected successfully",
    "data": {
        "id": 456,
        "user_id": 123,
        "market_id": 1,
        "order_id": "uuid-order-456",
        "type": "limit",
        "side": "buy",
        "quantity": "0.50000000",
        "price": "44000.00000000",
        "filled_quantity": "0.00000000",
        "remaining_quantity": "0.50000000",
        "average_price": "0.00000000",
        "total_value": "22000.00000000",
        "fee_amount": "0.00000000",
        "fee_currency": "USDT",
        "status": "cancelled",
        "cancelled_at": "2025-06-16T22:00:00Z",
        "cancel_reason": "Rejected by admin",
        "created_at": "2025-06-16T21:45:00Z",
        "updated_at": "2025-06-16T22:00:00Z",
        "user": {
            "id": 123,
            "name": "John Doe",
            "email": "john@example.com"
        },
        "market": {
            "id": 1,
            "symbol": "BTC/USDT",
            "display_name": "Bitcoin / Tether",
            "current_price": "46000.00000000"
        }
    }
}
```

### Error Responses
**400 Bad Request (Order cannot be rejected):**
```json
{
    "success": false,
    "message": "Order cannot be rejected. Current status: filled"
}
```

**404 Order Not Found:**
```json
{
    "success": false,
    "message": "Order not found"
}
```

---

## 4. Mark Order as Filled
**POST** `/v1/orders/{id}/fill`

Marks an order as filled (completely or partially) and updates fill statistics.

### URL Parameters
| Parameter | Type | Description |
|-----------|------|-------------|
| `id` | integer | Order ID |

### Request Body (Optional)
```json
{
    "fill_price": 45500.00,
    "fill_quantity": 0.25
}
```

### Request Fields
| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `fill_price` | number | ❌ | Price at which order was filled (defaults to order price) |
| `fill_quantity` | number | ❌ | Quantity filled (defaults to remaining quantity) |

### Example Requests
```bash
# Fill order completely at order price
POST /v1/orders/456/fill

# Fill order partially at specific price
POST /v1/orders/456/fill
Content-Type: application/json

{
    "fill_price": 45500.00,
    "fill_quantity": 0.25
}
```

### Success Response (200)
```json
{
    "success": true,
    "message": "Order marked as filled successfully",
    "data": {
        "id": 456,
        "user_id": 123,
        "market_id": 1,
        "order_id": "uuid-order-456",
        "type": "limit",
        "side": "buy",
        "quantity": "0.50000000",
        "price": "44000.00000000",
        "filled_quantity": "0.25000000",
        "remaining_quantity": "0.25000000",
        "average_price": "45500.00000000",
        "total_value": "11375.00000000",
        "fee_amount": "11.37500000",
        "fee_currency": "USDT",
        "status": "partially_filled",
        "executed_at": "2025-06-16T22:00:00Z",
        "created_at": "2025-06-16T21:45:00Z",
        "updated_at": "2025-06-16T22:00:00Z",
        "user": {
            "id": 123,
            "name": "John Doe",
            "email": "john@example.com"
        },
        "market": {
            "id": 1,
            "symbol": "BTC/USDT",
            "display_name": "Bitcoin / Tether",
            "current_price": "46000.00000000"
        }
    }
}
```

### Error Responses
**400 Bad Request (Order cannot be filled):**
```json
{
    "success": false,
    "message": "Order cannot be filled. Current status: cancelled"
}
```

**422 Validation Error:**
```json
{
    "success": false,
    "message": "Validation failed",
    "errors": {
        "fill_quantity": ["The fill quantity must be at least 0."]
    }
}
```

---

## 5. Get All Orders (Admin)
**GET** `/v1/orders/all`

Retrieves all orders across all users with pagination and filtering options. Perfect for admin dashboards.

### Query Parameters
| Parameter | Type | Optional | Description |
|-----------|------|----------|-------------|
| `per_page` | integer | ✅ | Results per page (default: 15) |
| `status` | string | ✅ | Filter by status: `pending`, `filled`, `cancelled`, `open` |
| `user_id` | integer | ✅ | Filter by specific user |
| `market_id` | integer | ✅ | Filter by specific market |

### Example Requests
```
GET /v1/orders/all
GET /v1/orders/all?status=pending
GET /v1/orders/all?status=open&per_page=25
GET /v1/orders/all?user_id=123&market_id=1
GET /v1/orders/all?status=filled&per_page=50
```

### Success Response (200)
```json
{
    "success": true,
    "message": "Orders retrieved successfully",
    "data": {
        "current_page": 1,
        "data": [
            {
                "id": 1,
                "user_id": 123,
                "market_id": 1,
                "order_id": "uuid-order-1",
                "type": "limit",
                "side": "buy",
                "quantity": "0.10000000",
                "price": "45000.00000000",
                "filled_quantity": "0.00000000",
                "remaining_quantity": "0.10000000",
                "status": "pending",
                "created_at": "2025-06-16T21:30:00Z",
                "updated_at": "2025-06-16T21:30:00Z",
                "user": {
                    "id": 123,
                    "name": "John Doe",
                    "email": "john@example.com"
                },
                "market": {
                    "id": 1,
                    "symbol": "BTC/USDT",
                    "display_name": "Bitcoin / Tether",
                    "current_price": "46000.00000000"
                }
            }
        ],
        "first_page_url": "http://localhost:8000/v1/orders/all?page=1",
        "from": 1,
        "last_page": 1,
        "last_page_url": "http://localhost:8000/v1/orders/all?page=1",
        "links": [...],
        "next_page_url": null,
        "path": "http://localhost:8000/v1/orders/all",
        "per_page": 15,
        "prev_page_url": null,
        "to": 1,
        "total": 1
    }
}
```

---

## 📊 Fund Allocation Integration

When orders are edited, the system automatically handles fund allocation changes:

### **Buy Orders:**
- **Currency Reserved**: Quote currency (e.g., USDT for BTC/USDT)
- **Amount Reserved**: `quantity × price`
- **On Edit**: Adjusts USDT allocation based on new quantity/price

### **Sell Orders:**
- **Currency Reserved**: Base currency (e.g., BTC for BTC/USDT)
- **Amount Reserved**: `quantity`
- **On Edit**: Adjusts BTC allocation based on new quantity

### **Edit Process:**
1. **Calculate Changes**: Compare current vs new allocations
2. **Validate Funds**: Check if user has sufficient balance for increased allocation
3. **Update Order**: Modify order details
4. **Reallocate Funds**: Update fund allocation record
5. **Reflect in API**: Changes immediately visible in User Funds API

### **Real-time Updates:**
The User Funds API (`/v1/funds/user/{userId}`) immediately reflects:
- ✅ **Available Funds**: Reduced/increased based on allocation changes
- ✅ **Allocated Funds**: Shows updated order reservations
- ✅ **Total Portfolio**: Accurate fund distribution

---

## Order Status Flow

### **Order Lifecycle:**
1. **Created** → `status: "pending"`
2. **Partially Filled** → `status: "partially_filled"`
3. **Completely Filled** → `status: "filled"`
4. **Cancelled/Rejected** → `status: "cancelled"`

### **Open Orders:**
Orders with status `pending` or `partially_filled` are considered "open" and can be:
- ✅ Rejected (cancelled)
- ✅ Filled (completely or partially)

### **Closed Orders:**
Orders with status `filled` or `cancelled` cannot be modified.

---

## Order Types & Sides

### **Order Types:**
- **`limit`** - Order executed at specific price or better
- **`market`** - Order executed immediately at current market price
- **`stop`** - Order triggered when price reaches stop level
- **`stop_limit`** - Combination of stop and limit orders

### **Order Sides:**
- **`buy`** - Purchase order (long position)
- **`sell`** - Sale order (short position)

---

## Admin Dashboard Use Cases

### **Order Management Dashboard**
```javascript
// Get all pending orders for admin review
const pendingOrders = await fetch('/v1/orders/all?status=pending');

// Filter orders by specific user for customer service
const userOrders = await fetch('/v1/orders/all?user_id=123');

// View orders for specific trading pair
const btcOrders = await fetch('/v1/orders/all?market_id=1');
```

### **Order Processing Workflow**
```javascript
// 1. Admin reviews pending orders
const orders = await fetch('/v1/orders/all?status=pending');

// 2. Reject suspicious order
await fetch('/v1/orders/456/reject', { method: 'POST' });

// 3. Fill legitimate order at market price
await fetch('/v1/orders/789/fill', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({
        fill_price: 46000.00,
        fill_quantity: 0.5
    })
});
```

### **User Support**
```javascript
// Check all orders for specific user
const userOrders = await fetch('/v1/orders/user/123');

// View order history with pagination
const recentOrders = await fetch('/v1/orders/all?user_id=123&per_page=50');
```

---

## Error Handling

### **Common HTTP Status Codes:**
- **200** - Success
- **400** - Bad Request (invalid operation)
- **404** - Order/User not found
- **422** - Validation error
- **500** - Server error

### **Error Response Format:**
```json
{
    "success": false,
    "message": "Error description",
    "error": "Detailed error message (on 500 errors)"
}
```

---

## Database Fields Reference

### **Core Order Fields:**
| Field | Type | Description |
|-------|------|-------------|
| `order_id` | string | Unique UUID identifier |
| `type` | enum | Order type (limit, market, stop, etc.) |
| `side` | enum | buy or sell |
| `quantity` | decimal | Total order size |
| `price` | decimal | Order price (limit orders) |
| `filled_quantity` | decimal | Amount already filled |
| `remaining_quantity` | decimal | Amount still pending |
| `average_price` | decimal | Average fill price |
| `total_value` | decimal | Total order value |
| `status` | enum | pending, partially_filled, filled, cancelled |

### **Execution Fields:**
| Field | Type | Description |
|-------|------|-------------|
| `executed_at` | timestamp | When order was first filled |
| `cancelled_at` | timestamp | When order was cancelled |
| `cancel_reason` | string | Reason for cancellation |

---

## Performance Features

- ✅ **User & Market Data**: Always included in responses
- ✅ **Filtering & Pagination**: Efficient data retrieval
- ✅ **Order State Management**: Proper status transitions
- ✅ **Admin Controls**: Reject and fill capabilities
- ✅ **Audit Trail**: Complete order history tracking
- ✅ **Real-time Data**: Current market prices included

This API provides complete order management functionality for both user-facing applications and admin dashboards! 