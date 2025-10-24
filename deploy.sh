#!/bin/bash

# VPBank Lucky Draw - Deploy Script
echo "🚀 Starting deployment process..."

# Check git status
echo "📋 Checking git status..."
git status

# Add all files
echo "📁 Adding all files..."
git add .

# Commit with descriptive message
echo "💾 Committing changes..."
git commit -m "Fix prize_analytics duplicate records issue

- Add UNIQUE constraint to prize_statistics.prize_id
- Fix trigger syntax from \$\$ to //
- Resolve 9 records issue in prize_analytics view
- Ready for production deployment"

# Push to GitHub
echo "🚀 Pushing to GitHub..."
git push origin main

echo "✅ Deployment completed successfully!"
echo "📝 Next steps:"
echo "   1. Deploy database_fixed.sql to production"
echo "   2. Test the application"
echo "   3. Monitor for any issues"
