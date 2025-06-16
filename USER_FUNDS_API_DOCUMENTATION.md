# 💼 User Funds & Portfolio API Documentation

## Overview
The User Funds API provides comprehensive portfolio information for users, including total portfolio value, P&L calculations, fund distribution across currencies and active trades, and trading activity metrics.

## Endpoint

### **GET `/v1/funds/user/{userId}`** - Get User Portfolio & Funds

Retrieves complete portfolio analysis including available funds, active trades, P&L calculations, and trading statistics.

### URL Parameters
| Parameter | Type | Description |
|-----------|------|-------------|
| `userId` | integer | User ID |

### Example Request
```
GET /v1/funds/user/123
```

### Success Response (200)
```json
{
    "success": true,
    "message": "User funds retrieved successfully",
    "data": {
        "user": {
            "id": 123,
            "name": "John Doe",
            "email": "john@example.com"
        },
        "portfolio_summary": {
            "total_portfolio_value": 15750.50,
            "available_funds": 10000.00,
            "margin_used": 5000.00,
            "unrealized_pnl": 750.50,
            "all_time_pnl": 1250.75,
            "realized_pnl": 500.25,
            "last_30_days_pnl": 325.80
        },
        "fund_distribution": {
            "available_cash": [
                {
                    "currency": "USDT",
                    "amount_usd": 5000.00,
                    "status": "available"
                },
                {
                    "currency": "BTC",
                    "amount_usd": 3000.00,
                    "status": "available"
                },
                {
                    "currency": "ETH",
                    "amount_usd": 2000.00,
                    "status": "available"
                }
            ],
            "total_available_usd": 10000.00
        },
        "active_trades": {
            "positions": [
                {
                    "position_id": "pos-uuid-123",
                    "market": "BTC/USDT",
                    "side": "long",
                    "entry_price": "45000.00000000",
                    "current_price": "46500.00000000",
                    "quantity": "0.10000000",
                    "margin_used": 2500.00,
                    "unrealized_pnl": 150.00,
                    "leverage": 2.00
                },
                {
                    "position_id": "pos-uuid-456",
                    "market": "ETH/USDT",
                    "side": "short",
                    "entry_price": "3200.00000000",
                    "current_price": "3100.00000000",
                    "quantity": "0.80000000",
                    "margin_used": 2500.00,
                    "unrealized_pnl": 600.50,
                    "leverage": 3.00
                }
            ],
            "total_positions": 2,
            "total_margin_used": 5000.00,
            "total_unrealized_pnl": 750.50
        },
        "trading_activity": {
            "open_orders_count": 3,
            "open_positions_count": 2,
            "total_closed_positions": 15
        },
        "performance_metrics": {
            "portfolio_change_percentage": 12.51,
            "win_rate": 73.33,
            "avg_position_size": 2500.00
        }
    }
}
```

---

## Data Structure Breakdown

### **1. Portfolio Summary**
The core financial overview of the user's trading account.

| Field | Description |
|-------|-------------|
| `total_portfolio_value` | Total value including available funds + margin used + unrealized P&L |
| `available_funds` | Cash available for trading (sum of all user_funds) |
| `margin_used` | Total margin currently locked in open positions |
| `unrealized_pnl` | Current profit/loss from open positions |
| `all_time_pnl` | Total P&L since account creation (realized + unrealized) |
| `realized_pnl` | Actual profit/loss from closed positions |
| `last_30_days_pnl` | P&L from positions closed in the last 30 days |

### **2. Fund Distribution**
Shows how user's available funds are split across different cryptocurrencies.

| Field | Description |
|-------|-------------|
| `available_cash` | Array of currency balances available for trading |
| `total_available_usd` | Sum of all available funds in USD equivalent |

**Available Cash Object:**
- `currency`: Cryptocurrency symbol (BTC, ETH, USDT, etc.)
- `amount_usd`: USD equivalent value
- `status`: Always "available" for cash

### **3. Active Trades**
Details of all open trading positions.

| Field | Description |
|-------|-------------|
| `positions` | Array of open position details |
| `total_positions` | Number of currently open positions |
| `total_margin_used` | Total margin locked across all positions |
| `total_unrealized_pnl` | Sum of unrealized P&L from all positions |

**Position Object:**
- `position_id`: Unique position identifier
- `market`: Trading pair symbol (e.g., BTC/USDT)
- `side`: "long" or "short"
- `entry_price`: Price at which position was opened
- `current_price`: Current market price
- `quantity`: Position size
- `margin_used`: Margin allocated to this position
- `unrealized_pnl`: Current profit/loss for this position
- `leverage`: Leverage multiplier used

### **4. Trading Activity**
Overview of user's trading activity and order management.

| Field | Description |
|-------|-------------|
| `open_orders_count` | **Number of pending/partially filled orders** |
| `open_positions_count` | Number of active trading positions |
| `total_closed_positions` | Historical count of closed positions |

### **5. Performance Metrics**
Key performance indicators for trading analysis.

| Field | Description |
|-------|-------------|
| `portfolio_change_percentage` | Overall portfolio performance as percentage |
| `win_rate` | Percentage of profitable closed positions |
| `avg_position_size` | Average margin used per position |

---

## Database Analysis

This endpoint analyzes data from multiple tables:

### **Tables Used:**
1. **`user_funds`** - Available cash balances by currency
2. **`positions`** - Open and closed trading positions for P&L calculation
3. **`orders`** - Count of pending orders
4. **`markets`** - Current market prices for position valuation
5. **`users`** - User information

### **Key Calculations:**

**Total Portfolio Value:**
```
available_funds + margin_used + unrealized_pnl
```

**All-Time P&L:**
```
sum(realized_pnl from all positions) + sum(unrealized_pnl from open positions)
```

**Win Rate:**
```
(count of profitable closed positions / total closed positions) × 100
```

---

## Use Cases

### **Dashboard Overview**
Perfect for displaying comprehensive portfolio information on user dashboards.

### **Risk Management**
- Monitor margin usage across positions
- Track unrealized P&L for risk assessment
- Analyze win rates and performance metrics

### **Fund Management**
- See available cash for new trades
- Understand fund allocation across currencies
- Monitor locked funds in active trades

### **Performance Analytics**
- Track all-time and recent P&L
- Calculate portfolio growth percentage
- Analyze trading success rates

---

## Error Responses

### **404 User Not Found**
```json
{
    "success": false,
    "message": "User not found"
}
```

### **500 Server Error**
```json
{
    "success": false,
    "message": "Failed to retrieve user funds",
    "error": "Database connection failed"
}
```

---

## Usage Examples

### **Portfolio Dashboard**
```javascript
// Get complete portfolio overview
const userFunds = await fetch('/v1/funds/user/123');
const data = await userFunds.json();

// Display key metrics
const totalValue = data.data.portfolio_summary.total_portfolio_value;
const availableCash = data.data.portfolio_summary.available_funds;
const openOrders = data.data.trading_activity.open_orders_count;
const pnl = data.data.portfolio_summary.all_time_pnl;
```

### **Risk Monitoring**
```javascript
// Check margin usage
const marginUsed = data.data.portfolio_summary.margin_used;
const availableFunds = data.data.portfolio_summary.available_funds;
const marginRatio = (marginUsed / (marginUsed + availableFunds)) * 100;

// Monitor unrealized losses
const unrealizedPnL = data.data.portfolio_summary.unrealized_pnl;
```

### **Trading Interface**
```javascript
// Show available funds for new trades
const availableCurrencies = data.data.fund_distribution.available_cash;
const openOrdersCount = data.data.trading_activity.open_orders_count;
```

---

## Performance Features

- ✅ **Single Request**: All portfolio data in one API call
- ✅ **Real-time Calculations**: Live P&L and portfolio values
- ✅ **Currency Breakdown**: Detailed fund distribution
- ✅ **Trading Activity**: Complete activity overview
- ✅ **Performance Metrics**: Win rate and growth calculations
- ✅ **Order Tracking**: Current open orders count
- ✅ **Position Analysis**: Detailed active trade information

This endpoint provides everything needed for comprehensive portfolio management and trading dashboards! 