# AI Sales Assistant ChatBot Setup

## Overview
The AI Sales Assistant is a floating chatbot integrated into the admin dashboard that provides intelligent sales insights, strategies, and recommendations based on your ECPOS system data.

## Features
- 📊 Real-time sales data analysis
- 💡 Sales strategy recommendations
- 📈 Performance insights and trends
- 🏪 Store comparison and optimization
- 🛍️ Product analysis and suggestions
- 📱 Responsive floating UI (bottom-right corner)

## Setup Instructions

### 1. OpenAI API Configuration
1. Get your OpenAI API key from https://platform.openai.com/api-keys
2. Update your `.env` file:
```env
OPENAI_API_KEY=your_actual_openai_api_key_here
OPENAI_MODEL=gpt-3.5-turbo
```

### 2. Access the ChatBot
- Navigate to `/admin` or `/dashboard` 
- Look for the floating blue chat bubble in the bottom-right corner
- Click to open and start chatting!

## ChatBot Capabilities

### Sales Analysis
- "How are our sales performing today compared to yesterday?"
- "What are our top-selling items this month?"
- "Show me our sales trends"

### Store Performance
- "Which stores are performing the best?"
- "How can we improve store X's performance?"
- "Compare sales across all locations"

### Strategic Insights
- "Can you suggest strategies to increase our daily sales?"
- "What promotional strategies would work for our business?"
- "How can we optimize our product mix?"

### Inventory & Products
- "What are our best selling products?"
- "How can we improve our inventory management?"
- "Which products should we focus on promoting?"

## Technical Details

### Backend Components
- `ChatBotService.php` - Handles OpenAI API communication and sales data integration
- `ChatBotController.php` - Manages API endpoints for the chatbot
- Routes: `/chatbot/send-message`, `/chatbot/welcome`, `/chatbot/sample-questions`

### Frontend Components
- `FloatingChatBot.vue` - Responsive floating chat interface
- Integrated with existing sales charts and data
- Auto-scrolling messages with typing indicators

### Data Integration
The chatbot has access to:
- Today's sales metrics (gross, net, discount amounts)
- Historical sales comparisons
- Top performing stores and products
- Sales trends and patterns
- Transaction counts and averages

## Usage Tips
1. **Start with sample questions** - Click on suggested questions for quick insights
2. **Be specific** - Ask about particular time periods, stores, or products
3. **Ask for explanations** - Request details about trends or recommendations
4. **Use quick actions** - Use the bottom quick action buttons for common queries

## Troubleshooting

### ChatBot Not Responding
1. Check if `OPENAI_API_KEY` is set correctly in `.env`
2. Verify internet connection
3. Check Laravel logs for API errors: `storage/logs/laravel.log`

### Missing Sales Data
- Ensure `rbotransactionsalestrans` table has recent data
- Check database connection
- Verify date formats in queries

### UI Issues
- Clear browser cache
- Rebuild assets: `npm run build`
- Check for JavaScript console errors

## API Costs
- Using GPT-3.5-turbo model for cost efficiency
- Each message costs approximately $0.0015-$0.002
- Messages are limited to 1000 tokens to control costs
- Context includes real-time sales data for relevant responses

## Security
- API key is stored securely in environment variables
- All requests are authenticated through Laravel middleware
- Sales data is processed server-side before sending to OpenAI
- No sensitive customer data is shared with the AI service

## Support
For technical issues or feature requests, check the Laravel logs and ensure all dependencies are properly installed.