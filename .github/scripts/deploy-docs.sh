#!/bin/bash

# Deploy API Documentation to GitHub Pages
# Usage: ./.github/scripts/deploy-docs.sh

set -e

echo "🚀 Deploying API Documentation to GitHub Pages..."

# Safety checks
echo "🔍 Running safety checks..."

# Check if we're in a git repository
if [ ! -d ".git" ]; then
    echo "❌ Error: Not in a git repository"
    exit 1
fi

# Check for uncommitted changes
if ! git diff-index --quiet HEAD --; then
    echo "⚠️  WARNING: You have uncommitted changes!"
    echo "📋 Uncommitted files:"
    git diff --name-only HEAD
    echo ""
    read -p "Do you want to commit these changes first? (y/n): " -n 1 -r
    echo ""
    if [[ $REPLY =~ ^[Yy]$ ]]; then
        echo "💾 Please commit your changes first, then run this script again."
        echo "Example: git add . && git commit -m 'Your commit message'"
        exit 1
    else
        echo "⚠️  Continuing without committing changes..."
        echo "💡 Your uncommitted changes will be stashed and restored after deployment."
        git stash push -m "Auto-stash before docs deployment $(date)"
        STASHED_CHANGES=true
    fi
fi

# Remember current branch
CURRENT_BRANCH=$(git branch --show-current)
echo "📍 Current branch: $CURRENT_BRANCH"

# Check if api-docs.json exists
if [ ! -f "storage/api-docs/api-docs.json" ]; then
    echo "❌ api-docs.json not found. Generating documentation..."
    php artisan l5-swagger:generate
fi

# Create temporary docs directory
DOCS_DIR="temp-docs-$(date +%s)"
rm -rf $DOCS_DIR
mkdir -p $DOCS_DIR/swagger-ui

echo "📋 Copying API documentation..."
cp storage/api-docs/api-docs.json $DOCS_DIR/

echo "📥 Downloading Swagger UI..."
curl -sL https://github.com/swagger-api/swagger-ui/archive/refs/tags/v5.27.0.tar.gz | tar -xz
cp -r swagger-ui-5.27.0/dist/* $DOCS_DIR/swagger-ui/
rm -rf swagger-ui-5.27.0

echo "🌐 Creating HTML files..."

# Create main index.html
cat > $DOCS_DIR/index.html << 'EOF'
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Job Portal API Documentation</title>
  <link rel="stylesheet" type="text/css" href="swagger-ui/swagger-ui-bundle.css" />
  <link rel="stylesheet" type="text/css" href="swagger-ui/swagger-ui-standalone-preset.css" />
  <style>
    html { box-sizing: border-box; overflow: -moz-scrollbars-vertical; overflow-y: scroll; }
    *, *:before, *:after { box-sizing: inherit; }
    body { margin:0; background: #fafafa; }
    .swagger-ui .topbar { display: none; }
    .swagger-ui .info { margin: 20px 0; }
    .swagger-ui .info .title { color: #3b4151; }
  </style>
</head>
<body>
  <div id="swagger-ui"></div>
  <script src="swagger-ui/swagger-ui-bundle.js"></script>
  <script src="swagger-ui/swagger-ui-standalone-preset.js"></script>
  <script>
    window.onload = function() {
      const ui = SwaggerUIBundle({
        url: './api-docs.json',
        dom_id: '#swagger-ui',
        deepLinking: true,
        presets: [SwaggerUIBundle.presets.apis, SwaggerUIStandalonePreset],
        plugins: [SwaggerUIBundle.plugins.DownloadUrl],
        layout: "StandaloneLayout",
        validatorUrl: null,
        tryItOutEnabled: true,
        supportedSubmitMethods: ['get', 'post', 'put', 'delete', 'patch'],
        docExpansion: 'list',
        filter: true,
        showRequestHeaders: true
      });
    };
  </script>
</body>
</html>
EOF

# Create JSON viewer page
cat > $DOCS_DIR/json.html << 'EOF'
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Job Portal API - OpenAPI JSON</title>
  <style>
    body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; margin: 0; background: #f8f9fa; }
    .container { max-width: 1200px; margin: 0 auto; padding: 20px; }
    .header { background: white; padding: 20px; border-radius: 8px; margin-bottom: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
    .actions { margin: 20px 0; }
    .btn { background: #007bff; color: white; padding: 12px 24px; text-decoration: none; border-radius: 6px; display: inline-block; margin-right: 10px; }
    .btn:hover { background: #0056b3; }
    .btn.secondary { background: #6c757d; }
    .json-container { background: white; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
    pre { margin: 0; padding: 20px; overflow: auto; font-size: 13px; line-height: 1.4; }
    .copy-btn { position: absolute; top: 10px; right: 10px; background: #28a745; color: white; border: none; padding: 8px 12px; border-radius: 4px; cursor: pointer; }
    .relative { position: relative; }
  </style>
</head>
<body>
  <div class="container">
    <div class="header">
      <h1>Job Portal API Documentation</h1>
      <p>OpenAPI 3.0 specification for the Job Portal API with RSA-512 JWT authentication</p>
    </div>
    
    <div class="actions">
      <a href="./" class="btn">📖 Interactive Documentation</a>
      <a href="./api-docs.json" class="btn secondary" download>⬇️ Download JSON</a>
      <button onclick="copyToClipboard()" class="btn secondary">📋 Copy JSON</button>
    </div>
    
    <div class="json-container relative">
      <button onclick="copyToClipboard()" class="copy-btn">Copy</button>
      <pre id="json-content">Loading...</pre>
    </div>
  </div>
  
  <script>
    let jsonData = '';
    
    fetch('./api-docs.json')
      .then(response => response.json())
      .then(data => {
        jsonData = JSON.stringify(data, null, 2);
        document.getElementById('json-content').textContent = jsonData;
      })
      .catch(error => {
        document.getElementById('json-content').textContent = 'Error loading JSON: ' + error;
      });
    
    function copyToClipboard() {
      navigator.clipboard.writeText(jsonData).then(() => {
        const btn = document.querySelector('.copy-btn');
        const originalText = btn.textContent;
        btn.textContent = 'Copied!';
        btn.style.background = '#28a745';
        setTimeout(() => {
          btn.textContent = originalText;
          btn.style.background = '#28a745';
        }, 2000);
      });
    }
  </script>
</body>
</html>
EOF

# Create README
cat > $DOCS_DIR/README.md << 'EOF'
# Job Portal API Documentation

This directory contains the OpenAPI 3.0 specification for the Job Portal API.

## Files

- `index.html` - Interactive Swagger UI documentation
- `json.html` - JSON viewer with copy/download functionality  
- `api-docs.json` - OpenAPI specification file
- `swagger-ui/` - Swagger UI assets

## Usage

### For Frontend Development
```javascript
// Fetch the OpenAPI specification
const apiSpec = await fetch('https://Job-Portal-Project.github.io/api/api-docs.json');
const spec = await apiSpec.json();
```

### For API Testing
Visit the interactive documentation at: `https://Job-Portal-Project.github.io/api/`

## Deployment

This documentation is automatically deployed via GitHub Actions when `storage/api-docs/api-docs.json` is updated.
EOF

# Deploy to gh-pages branch
echo "📤 Deploying to gh-pages branch..."

# Create or switch to gh-pages branch with error handling
if git show-ref --verify --quiet refs/heads/gh-pages; then
    echo "📍 Switching to existing gh-pages branch..."
    git checkout gh-pages
else
    echo "📍 Creating new gh-pages branch..."
    git checkout --orphan gh-pages
fi

# Remove all files except our docs (but preserve .git)
echo "🗑️  Cleaning gh-pages branch..."
git rm -rf . 2>/dev/null || true
rm -rf * 2>/dev/null || true

# Copy docs to root
echo "📁 Copying documentation files..."
cp -r $DOCS_DIR/* .

# Add and commit
echo "💾 Committing documentation..."
git add .
git commit -m "Deploy API documentation - $(date '+%Y-%m-%d %H:%M:%S')" || {
    echo "⚠️  No changes to commit"
}

echo "✅ Documentation committed to gh-pages branch"
echo ""
echo "📋 Next steps:"
echo "   1. Push the gh-pages branch: git push origin gh-pages"
echo "   2. Enable GitHub Pages in repository settings (if not done already)"
echo "   3. Set source to 'gh-pages' branch"
echo ""
echo "🌐 Your docs will be available at: https://Job-Portal-Project.github.io/api/"
echo "📖 Frontend team can access the API specification at the URL above"

# Switch back to original branch
echo "🔄 Switching back to $CURRENT_BRANCH branch..."
git checkout $CURRENT_BRANCH

# Restore stashed changes if any
if [ "$STASHED_CHANGES" = true ]; then
    echo "🔄 Restoring your uncommitted changes..."
    git stash pop
    echo "✅ Your changes have been restored"
fi

# Cleanup
echo "🧹 Cleaning up temporary files..."
rm -rf $DOCS_DIR

echo ""
echo "🎉 Deployment script completed successfully!"
echo "💡 Your working directory is back to how it was before deployment."