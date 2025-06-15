# 📈 CHART API Documentation

## Overview

The CHART API provides comprehensive charting and market data endpoints designed for building sophisticated trading charts and financial interfaces. This API offers real-time market data, OHLCV candlestick data, order book depth, and recent trades.

**Base URL**: `/api/v1/charts/`

## 🎯 Key Features

- **Real-time OHLCV data** for candlestick charts
- **Market ticker data** with bid/ask spreads and statistics
- **Order book depth** with realistic bid/ask levels
- **Recent trades** with buy/sell indicators
- **Multiple timeframes** (1m, 5m, 15m, 30m, 1h, 4h, 1d)
- **Symbol normalization** (supports both `BTC-USDT` and `BTC/USDT` formats)
- **Consistent JSON structure** across all endpoints
- **No authentication required** for public market data

---

## 📚 Endpoints Overview

| Endpoint | Method | Purpose |
|----------|--------|---------|
| `/{symbol}/candles` | GET | OHLCV candlestick data for charting |
| `/{symbol}/ticker` | GET | Market ticker with bid/ask and statistics |
| `/{symbol}/depth` | GET | Order book depth with bids and asks |
| `/{symbol}/trades` | GET | Recent trades with side indicators |

---

## 🚀 Endpoint Details

### 1. Candlestick Data

**Endpoint**: `GET /api/v1/charts/{symbol}/candles`

**Description**: Retrieves OHLCV (Open, High, Low, Close, Volume) candlestick data for charting applications.

#### Parameters

| Parameter | Type | Default | Description |
|-----------|------|---------|-------------|
| `symbol` | string | - | Market symbol (BTC-USDT, ETH-BTC, etc.) |
| `interval` | string | "1h" | Timeframe (1m, 5m, 15m, 30m, 1h, 4h, 1d) |
| `limit` | integer | 100 | Number of candles (max: 1000) |
| `startTime` | integer | - | Start timestamp in milliseconds |
| `endTime` | integer | - | End timestamp in milliseconds |

#### Example Request

```bash
GET /api/v1/charts/BTC-USDT/candles?interval=1h&limit=200&startTime=1672531200000
```

#### Example Response

```json
{
  "success": true,
  "message": "Candle data retrieved successfully",
  "data": {
    "symbol": "BTC/USDT",
    "interval": "1h",
    "candles": [
      [
        1672531200000,  // Timestamp (milliseconds)
        43250.75,       // Open
        43420.50,       // High
        43180.25,       // Low
        43380.80,       // Close
        1547.8542       // Volume
      ],
      [
        1672534800000,
        43380.80,
        43580.25,
        43320.15,
        43455.90,
        1623.7421
      ]
    ],
    "count": 200,
    "market_info": {
      "id": 1,
      "base_currency": "BTC",
      "quote_currency": "USDT",
      "current_price": 43455.90,
      "price_change_24h": 1205.15,
      "price_change_percentage_24h": 2.85
    }
  }
}
```

#### Use Cases

- **TradingView charts** and candlestick displays
- **Technical analysis** with indicators
- **Price history** visualization
- **Mobile trading apps** with compact charts

---

### 2. Market Ticker

**Endpoint**: `GET /api/v1/charts/{symbol}/ticker`

**Description**: Retrieves comprehensive ticker data including price, changes, volume, and bid/ask information.

#### Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `symbol` | string | Yes | Market symbol (BTC-USDT, ETH-BTC, etc.) |

#### Example Request

```bash
GET /api/v1/charts/BTC-USDT/ticker
```

#### Example Response

```json
{
  "success": true,
  "message": "Ticker data retrieved successfully",
  "data": {
    "symbol": "BTC/USDT",
    "price": 43455.90,
    "price_change": 1205.15,
    "price_change_percent": 2.85,
    "high_24h": 44120.50,
    "low_24h": 42180.25,
    "volume_24h": 15420000000,
    "market_cap": 847829384729,
    "last_updated": "2025-01-15T14:30:00.000000Z",
    "bid": 43433.70,
    "ask": 43478.10,
    "bid_qty": 2.5847,
    "ask_qty": 1.9234,
    "open_24h": 42250.75,
    "prev_close": 42250.75,
    "quote_volume_24h": 668750000000,
    "count": 125847,
    "is_active": true,
    "is_trading_enabled": true
  }
}
```

#### Use Cases

- **Price tickers** on exchange interfaces
- **Market overview** widgets
- **Trading terminal** displays
- **Portfolio tracking** applications

---

### 3. Order Book Depth

**Endpoint**: `GET /api/v1/charts/{symbol}/depth`

**Description**: Retrieves order book depth data with bid and ask levels for market depth visualization.

#### Parameters

| Parameter | Type | Default | Description |
|-----------|------|---------|-------------|
| `symbol` | string | - | Market symbol (BTC-USDT, ETH-BTC, etc.) |
| `limit` | integer | 20 | Number of price levels (max: 100) |

#### Example Request

```bash
GET /api/v1/charts/BTC-USDT/depth?limit=10
```

#### Example Response

```json
{
  "success": true,
  "message": "Order book data retrieved successfully",
  "data": {
    "symbol": "BTC/USDT",
    "last_update_id": 1673873400,
    "bids": [
      ["43433.70", "2.5847"],
      ["43420.85", "1.8923"],
      ["43407.95", "3.2156"],
      ["43395.10", "0.9874"],
      ["43382.25", "4.1523"]
    ],
    "asks": [
      ["43478.10", "1.9234"],
      ["43490.95", "2.7841"],
      ["43503.80", "1.4567"],
      ["43516.65", "3.8912"],
      ["43529.50", "2.1456"]
    ],
    "market_info": {
      "current_price": 43455.90,
      "spread": 44.40,
      "spread_percent": 0.102
    }
  }
}
```

#### Use Cases

- **Market depth charts** and order book visualization
- **Trading interfaces** showing available liquidity
- **Algorithmic trading** order placement strategies
- **Market analysis** tools

---

### 4. Recent Trades

**Endpoint**: `GET /api/v1/charts/{symbol}/trades`

**Description**: Retrieves recent trade history with price, quantity, time, and side information.

#### Parameters

| Parameter | Type | Default | Description |
|-----------|------|---------|-------------|
| `symbol` | string | - | Market symbol (BTC-USDT, ETH-BTC, etc.) |
| `limit` | integer | 50 | Number of trades (max: 500) |

#### Example Request

```bash
GET /api/v1/charts/BTC-USDT/trades?limit=25
```

#### Example Response

```json
{
  "success": true,
  "message": "Recent trades retrieved successfully",
  "data": {
    "symbol": "BTC/USDT",
    "trades": [
      {
        "id": 1673873456,
        "price": "43455.90",
        "qty": "0.2547",
        "quote_qty": "11069.84",
        "time": 1673873456000,
        "is_buyer_maker": true,
        "side": "BUY"
      },
      {
        "id": 1673873455,
        "price": "43448.75",
        "qty": "1.8923",
        "quote_qty": "82241.18",
        "time": 1673873455000,
        "is_buyer_maker": false,
        "side": "SELL"
      }
    ],
    "count": 25
  }
}
```

---

**Last Updated**: January 15, 2025  
**API Version**: 1.0  
**Documentation Version**: 1.0 