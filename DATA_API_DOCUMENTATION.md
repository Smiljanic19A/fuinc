# 📊 DATA API Documentation

## Overview

The DATA API provides comprehensive cryptocurrency market data endpoints designed for high-performance frontend applications. This API group offers enhanced analytics, real-time market insights, and aggregated data for building sophisticated trading interfaces and dashboards.

**Base URL**: `/api/v1/data/`

## 🎯 Key Features

- **Real-time crypto data** from CoinCap API integration
- **Advanced analytics** including heat scores, volatility analysis, and trend detection
- **Optimized responses** for frontend performance
- **Comprehensive market insights** with support/resistance levels
- **Consistent JSON structure** across all endpoints
- **No authentication required** for public market data

---

## 📚 Endpoints Overview

| Endpoint | Method | Purpose |
|----------|--------|---------|
| `/top-performers` | GET | Top performing coins by price change |
| `/hot-coins` | GET | Hot coins with enhanced analytics |
| `/market/{id}` | GET | Single market comprehensive data |
| `/overview` | GET | Complete dashboard overview |

---

## 🚀 Endpoint Details

### 1. Top Performers

**Endpoint**: `GET /api/v1/data/top-performers`

**Description**: Retrieves the top performing cryptocurrencies based on 24-hour price change percentage.

#### Parameters

| Parameter | Type | Default | Description |
|-----------|------|---------|-------------|
| `limit` | integer | 10 | Number of coins to return (max: 50) |
| `timeframe` | string | "24h" | Time period (currently supports 24h) |

#### Example Request

```bash
GET /api/v1/data/top-performers?limit=10&timeframe=24h
```

#### Example Response

```json
{
  "success": true,
  "message": "Top 10 performers retrieved successfully",
  "data": {
    "timeframe": "24h",
    "performers": [
      {
        "id": 1,
        "symbol": "BTC",
        "name": "Bitcoin",
        "full_name": "Bitcoin (BTC)",
        "current_price": 43250.75,
        "price_change_24h": 1250.75,
        "price_change_percentage_24h": 2.98,
        "volume_24h": 15420000000,
        "market_cap": 847829384729,
        "market_cap_rank": 1,
        "category": "Store of Value",
        "blockchain": "Bitcoin",
        "icon_url": "/images/coins/btc.png",
        "is_hot": true,
        "is_trending": true,
        "tags": ["Store of Value", "Digital Gold"]
      }
    ],
    "count": 10
  }
}
```

#### Use Cases

- **Gainers section** on trading dashboards
- **Performance leaderboards** 
- **Market trending indicators**
- **Portfolio performance comparison**

---

### 2. Hot Coins

**Endpoint**: `GET /api/v1/data/hot-coins`

**Description**: Retrieves hot coins with enhanced analytics including heat scores, statistics, and category breakdowns.

#### Parameters

| Parameter | Type | Default | Description |
|-----------|------|---------|-------------|
| `limit` | integer | 20 | Number of hot coins to return (max: 100) |

#### Example Request

```bash
GET /api/v1/data/hot-coins?limit=20
```

#### Example Response

```json
{
  "success": true,
  "message": "Hot coins retrieved successfully",
  "data": {
    "coins": [
      {
        "id": 2,
        "symbol": "ETH",
        "name": "Ethereum",
        "full_name": "Ethereum (ETH)",
        "current_price": 2650.40,
        "price_change_24h": 85.40,
        "price_change_percentage_24h": 3.33,
        "volume_24h": 8750000000,
        "market_cap": 318000000000,
        "market_cap_rank": 2,
        "circulating_supply": 120000000,
        "category": "Smart Contracts",
        "blockchain": "Ethereum",
        "icon_url": "/images/coins/eth.png",
        "website_url": "https://ethereum.org",
        "is_hot": true,
        "is_new": false,
        "is_trending": true,
        "tags": ["Smart Contracts", "DeFi", "NFTs"],
        "launched_at": "2015-07-30T00:00:00.000000Z",
        "heat_score": 78.45
      }
    ],
    "count": 20,
    "stats": {
      "average_gain": 8.67,
      "total_volume": 45750000000,
      "categories": {
        "Smart Contracts": 8,
        "DeFi": 5,
        "Meme": 3,
        "Layer 2": 2,
        "Store of Value": 2
      }
    }
  }
}
```

#### Heat Score Algorithm

The heat score (0-100) is calculated using:

- **Price Change (40%)**: Normalized 24h price change percentage
- **Volume (30%)**: Trading volume relative to 1B benchmark  
- **Market Cap Rank (20%)**: Position in market cap rankings
- **Status Flags (10%)**: Bonus points for hot/trending/new flags

```javascript
// Heat Score Formula
priceScore = min(abs(price_change_24h) / 20, 1) * 40
volumeScore = min(volume_24h / 1_000_000_000, 1) * 30  
rankScore = max(0, (100 - market_cap_rank) / 100) * 20
flagScore = (is_hot ? 3 : 0) + (is_trending ? 4 : 0) + (is_new ? 3 : 0)

heat_score = priceScore + volumeScore + rankScore + flagScore
```

#### Use Cases

- **Hot coins section** on exchange homepages
- **Trending alerts** and notifications
- **Discovery features** for new opportunities
- **Heat maps** and visual indicators

---

### 3. Single Market

**Endpoint**: `GET /api/v1/data/market/{id}`

**Description**: Retrieves comprehensive data for a single market including analytics, trends, and recent price action.

#### Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `id` | integer | Yes | Market ID |

#### Example Request

```bash
GET /api/v1/data/market/1
```

#### Example Response

```json
{
  "success": true,
  "message": "Market data retrieved successfully",
  "data": {
    "market": {
      "id": 1,
      "symbol": "BTC/USDT",
      "base_currency": "BTC",
      "quote_currency": "USDT",
      "display_name": "Bitcoin/USDT",
      "current_price": 43250.75,
      "price_change_24h": 1250.75,
      "price_change_percentage_24h": 2.98,
      "high_24h": 44120.50,
      "low_24h": 41890.25,
      "volume_24h": 15420000000,
      "market_cap": 847829384729,
      "rank": 1,
      "min_order_amount": 0.00001,
      "max_order_amount": 1000,
      "price_precision": 2,
      "quantity_precision": 5,
      "is_active": true,
      "is_trading_enabled": true,
      "icon_url": "/images/coins/btc.png",
      "description": "Trade Bitcoin against USDT"
    },
    "base_coin": {
      "id": 1,
      "symbol": "BTC",
      "name": "Bitcoin",
      "full_name": "Bitcoin (BTC)",
      "description": "Bitcoin is the world's first cryptocurrency...",
      "current_price": 43250.75,
      "market_cap": 847829384729,
      "volume_24h": 15420000000,
      "price_change_percentage_24h": 2.98,
      "market_cap_rank": 1,
      "circulating_supply": 19600000,
      "total_supply": 19600000,
      "max_supply": 21000000,
      "is_hot": true,
      "is_new": false,
      "is_trending": true,
      "category": "Store of Value",
      "blockchain": "Bitcoin",
      "tags": ["Store of Value", "Digital Gold"],
      "icon_url": "/images/coins/btc.png",
      "website_url": "https://bitcoin.org",
      "launched_at": "2009-01-03T00:00:00.000000Z"
    },
    "quote_coin": {
      "id": 3,
      "symbol": "USDT",
      "name": "Tether",
      "full_name": "Tether USD (USDT)",
      "current_price": 1.00,
      "is_hot": false,
      "category": "Stablecoin"
    },
    "analytics": {
      "volume_avg_24h": 642500000,
      "price_volatility": 2.1847,
      "candles_count": 24,
      "trend": "bullish",
      "support_resistance": {
        "support": 41890.25,
        "resistance": 44120.50
      }
    },
    "recent_candles": [
      {
        "timestamp": "2025-06-15T08:00:00.000000Z",
        "open": 43100.25,
        "high": 43280.50,
        "low": 43050.00,
        "close": 43250.75,
        "volume": 642500000
      }
    ]
  }
}
```

#### Analytics Explained

##### Volatility Calculation
```javascript
// Price volatility is calculated using standard deviation of returns
returns = prices.map((price, i) => i > 0 ? (price - prices[i-1]) / prices[i-1] : 0)
meanReturn = returns.reduce((a, b) => a + b) / returns.length
variance = returns.map(r => Math.pow(r - meanReturn, 2)).reduce((a, b) => a + b) / returns.length
volatility = Math.sqrt(variance) * 100 // Convert to percentage
```

##### Trend Detection
- **Bullish**: Last 3 candle closes are ascending
- **Bearish**: Last 3 candle closes are descending  
- **Neutral**: Mixed or insufficient data

##### Support/Resistance
- **Support**: Lowest low from recent 24 hourly candles
- **Resistance**: Highest high from recent 24 hourly candles

#### Use Cases

- **Market detail pages** with comprehensive data
- **Trading interfaces** with analytics
- **Price alerts** and monitoring
- **Technical analysis** integration

---

### 4. Overview Dashboard

**Endpoint**: `GET /api/v1/data/overview`

**Description**: Provides complete market overview data for dashboards, including market statistics, top performers, losers, hot coins, and trending markets.

#### Parameters

None required.

#### Example Request

```bash
GET /api/v1/data/overview
```

#### Example Response

```json
{
  "success": true,
  "message": "Market overview retrieved successfully",
  "data": {
    "market_stats": {
      "total_market_cap": 2847829384729,
      "total_volume_24h": 89750000000,
      "total_coins": 100,
      "total_markets": 297,
      "last_updated": "2025-06-15T09:30:00.000000Z"
    },
    "top_performers": [
      {
        "id": 15,
        "symbol": "HYPE",
        "name": "Hyperliquid",
        "current_price": 25.67,
        "price_change_percentage_24h": 18.45,
        "icon_url": "/images/coins/hype.png"
      }
    ],
    "top_losers": [
      {
        "id": 8,
        "symbol": "ADA", 
        "name": "Cardano",
        "current_price": 0.48,
        "price_change_percentage_24h": -8.23,
        "icon_url": "/images/coins/ada.png"
      }
    ],
    "hot_coins": [
      {
        "id": 2,
        "symbol": "ETH",
        "name": "Ethereum", 
        "current_price": 2650.40,
        "price_change_percentage_24h": 3.33,
        "volume_24h": 8750000000,
        "icon_url": "/images/coins/eth.png"
      }
    ],
    "trending_markets": [
      {
        "id": 1,
        "symbol": "BTC/USDT",
        "display_name": "Bitcoin/USDT",
        "current_price": 43250.75,
        "price_change_percentage_24h": 2.98,
        "volume_24h": 15420000000,
        "base_coin": {
          "symbol": "BTC",
          "name": "Bitcoin",
          "icon_url": "/images/coins/btc.png"
        }
      }
    ]
  }
}
```

#### Use Cases

- **Dashboard homepage** with complete overview
- **Widget components** for embedding
- **Market summary** displays
- **Quick insights** for mobile apps

---

## 🛠️ Frontend Integration

### React.js Example

```javascript
import { useState, useEffect } from 'react';

const MarketDashboard = () => {
  const [overview, setOverview] = useState(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    const fetchOverview = async () => {
      try {
        const response = await fetch('/api/v1/data/overview');
        const data = await response.json();
        
        if (data.success) {
          setOverview(data.data);
        }
      } catch (error) {
        console.error('Failed to fetch market overview:', error);
      } finally {
        setLoading(false);
      }
    };

    fetchOverview();
    
    // Refresh every 30 seconds
    const interval = setInterval(fetchOverview, 30000);
    return () => clearInterval(interval);
  }, []);

  if (loading) return <div>Loading market data...</div>;

  return (
    <div className="market-dashboard">
      <div className="market-stats">
        <h2>Market Statistics</h2>
        <p>Total Market Cap: ${overview.market_stats.total_market_cap.toLocaleString()}</p>
        <p>24h Volume: ${overview.market_stats.total_volume_24h.toLocaleString()}</p>
        <p>Active Markets: {overview.market_stats.total_markets}</p>
      </div>
      
      <div className="top-performers">
        <h3>🚀 Top Performers</h3>
        {overview.top_performers.map(coin => (
          <div key={coin.id} className="performer-card">
            <img src={coin.icon_url} alt={coin.symbol} />
            <span>{coin.symbol}</span>
            <span className="gain">+{coin.price_change_percentage_24h}%</span>
          </div>
        ))}
      </div>
      
      <div className="hot-coins">
        <h3>🔥 Hot Coins</h3>
        {overview.hot_coins.map(coin => (
          <div key={coin.id} className="hot-coin-card">
            <img src={coin.icon_url} alt={coin.symbol} />
            <div>
              <h4>{coin.name}</h4>
              <p>${coin.current_price}</p>
              <span className={coin.price_change_percentage_24h > 0 ? 'gain' : 'loss'}>
                {coin.price_change_percentage_24h > 0 ? '+' : ''}{coin.price_change_percentage_24h}%
              </span>
            </div>
          </div>
        ))}
      </div>
    </div>
  );
};

export default MarketDashboard;
```

### Vue.js Example

```vue
<template>
  <div class="market-dashboard">
    <div v-if="loading" class="loading">Loading market data...</div>
    
    <div v-else class="dashboard-content">
      <MarketStats :stats="overview.market_stats" />
      <TopPerformers :performers="overview.top_performers" />
      <HotCoins :coins="overview.hot_coins" />
      <TrendingMarkets :markets="overview.trending_markets" />
    </div>
  </div>
</template>

<script>
import { ref, onMounted, onUnmounted } from 'vue';

export default {
  name: 'MarketDashboard',
  setup() {
    const overview = ref(null);
    const loading = ref(true);
    let refreshInterval;

    const fetchOverview = async () => {
      try {
        const response = await fetch('/api/v1/data/overview');
        const data = await response.json();
        
        if (data.success) {
          overview.value = data.data;
        }
      } catch (error) {
        console.error('Failed to fetch market overview:', error);
      } finally {
        loading.value = false;
      }
    };

    onMounted(() => {
      fetchOverview();
      refreshInterval = setInterval(fetchOverview, 30000);
    });

    onUnmounted(() => {
      if (refreshInterval) {
        clearInterval(refreshInterval);
      }
    });

    return {
      overview,
      loading
    };
  }
};
</script>
```

### JavaScript API Class

```javascript
class MarketAPI {
  constructor(baseUrl = '/api/v1/data') {
    this.baseUrl = baseUrl;
  }

  async getTopPerformers(limit = 10) {
    const response = await fetch(`${this.baseUrl}/top-performers?limit=${limit}`);
    return await response.json();
  }

  async getHotCoins(limit = 20) {
    const response = await fetch(`${this.baseUrl}/hot-coins?limit=${limit}`);
    return await response.json();
  }

  async getMarket(id) {
    const response = await fetch(`${this.baseUrl}/market/${id}`);
    return await response.json();
  }

  async getOverview() {
    const response = await fetch(`${this.baseUrl}/overview`);
    return await response.json();
  }
}

// Usage
const api = new MarketAPI();

// Load dashboard data
async function loadDashboard() {
  try {
    const [performers, hotCoins, overview] = await Promise.all([
      api.getTopPerformers(5),
      api.getHotCoins(10),
      api.getOverview()
    ]);

    updatePerformersSection(performers.data.performers);
    updateHotCoinsSection(hotCoins.data.coins);
    updateMarketStats(overview.data.market_stats);
  } catch (error) {
    console.error('Failed to load dashboard:', error);
  }
}

// Auto-refresh every 30 seconds
setInterval(loadDashboard, 30000);
loadDashboard();
```

---

## 🔧 Error Handling

All endpoints return consistent error responses:

### Error Response Format

```json
{
  "success": false,
  "message": "Error description",
  "errors": {
    "field": ["Validation error message"]
  }
}
```

### Common HTTP Status Codes

| Code | Meaning | Description |
|------|---------|-------------|
| 200 | OK | Request successful |
| 400 | Bad Request | Invalid parameters |
| 404 | Not Found | Market/resource not found |
| 422 | Unprocessable Entity | Validation errors |
| 500 | Internal Server Error | Server error |

### Error Handling Example

```javascript
const fetchMarketData = async (marketId) => {
  try {
    const response = await fetch(`/api/v1/data/market/${marketId}`);
    const data = await response.json();
    
    if (!data.success) {
      throw new Error(data.message);
    }
    
    return data.data;
  } catch (error) {
    if (error.response?.status === 404) {
      console.error('Market not found');
    } else {
      console.error('Failed to fetch market data:', error.message);
    }
    throw error;
  }
};
```

---

## 📈 Performance Optimization

### Caching Recommendations

```javascript
// Client-side caching with localStorage
class CachedMarketAPI {
  constructor() {
    this.cache = new Map();
    this.cacheDuration = 30000; // 30 seconds
  }

  async getOverview() {
    const cacheKey = 'market_overview';
    const cached = this.cache.get(cacheKey);
    
    if (cached && Date.now() - cached.timestamp < this.cacheDuration) {
      return cached.data;
    }

    const response = await fetch('/api/v1/data/overview');
    const data = await response.json();
    
    this.cache.set(cacheKey, {
      data,
      timestamp: Date.now()
    });
    
    return data;
  }
}
```

### Rate Limiting

- **Recommended refresh rate**: 30-60 seconds for overview data
- **Real-time updates**: Use WebSocket for price tickers
- **Burst requests**: Limit to 100 requests per minute per IP

---

## 🧪 Testing the API

### Using cURL

```bash
# Test top performers
curl -X GET "http://localhost:8000/api/v1/data/top-performers?limit=5"

# Test hot coins
curl -X GET "http://localhost:8000/api/v1/data/hot-coins?limit=10"

# Test single market
curl -X GET "http://localhost:8000/api/v1/data/market/1"

# Test overview
curl -X GET "http://localhost:8000/api/v1/data/overview"
```

### Response Validation

```javascript
// Validate API response structure
function validateApiResponse(response, expectedFields) {
  if (!response.success) {
    throw new Error(`API Error: ${response.message}`);
  }
  
  for (const field of expectedFields) {
    if (!(field in response.data)) {
      throw new Error(`Missing field: ${field}`);
    }
  }
  
  return true;
}

// Usage
const overview = await api.getOverview();
validateApiResponse(overview, ['market_stats', 'top_performers', 'hot_coins']);
```

---

## 📊 Data Freshness

### Real-time Data Flow

```
CoinCap API → Import Command → Database → DATA API → Frontend
     ↓              ↓              ↓           ↓          ↓
  Every hour    Manual/Cron   Live Storage   Cache     Real-time UI
```

### Update Frequency

- **Coin Prices**: Updated via import command (recommended: every 15-30 minutes)
- **Market Data**: Historical data imported with coins
- **Analytics**: Calculated in real-time from stored data
- **Cache**: Frontend should cache for 30-60 seconds

---

## 🔮 Future Enhancements

### Planned Features

1. **WebSocket Support**: Real-time price updates
2. **Historical Timeframes**: 7d, 30d performance data  
3. **Portfolio Tracking**: Personal portfolio analytics
4. **Price Alerts**: Threshold-based notifications
5. **Advanced Analytics**: RSI, MACD, moving averages
6. **Market Sentiment**: Social sentiment analysis
7. **News Integration**: Related news and events

### API Versioning

Future versions will maintain backward compatibility:

- `v1`: Current stable version
- `v2`: WebSocket + advanced analytics (planned)
- `v3`: AI-powered insights (future)

---

**Last Updated**: June 15, 2025  
**API Version**: 1.0  
**Documentation Version**: 1.0 