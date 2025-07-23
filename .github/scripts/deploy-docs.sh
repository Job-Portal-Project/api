#!/bin/bash

# Deploy API Documentation to GitHub Pages
# Usage: ./scripts/deploy-docs.sh

set -e

echo "🚀 Deploying API Documentation to GitHub Pages..."

# Check if api-docs.json exists
if [ ! -f "storage/api-docs/api-docs.json" ]; then
    echo "❌ api-docs.json not found. Generating documentation..."
    php artisan l5-swagger:generate
fi

# Create temporary docs directory
DOCS_DIR="temp-docs"
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

echo "📝 Creating README..."
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
const apiSpec = await fetch('https://your-username.github.io/jobportal/api-docs.json');
const spec = await apiSpec.json();
```

### For API Testing
Visit the interactive documentation at: `https://your-username.github.io/jobportal/`

## Deployment

This documentation is automatically deployed via GitHub Actions when `storage/api-docs/api-docs.json` is updated.
EOF

# Check if we're in a git repository
if [ -d ".git" ]; then
    echo "📤 Committing to gh-pages branch..."
    
    # Create or switch to gh-pages branch
    git checkout --orphan gh-pages 2>/dev/null || git checkout gh-pages
    
    # Remove all files except our docs
    git rm -rf . 2>/dev/null || true
    
    # Copy docs to root
    cp -r $DOCS_DIR/* .
    
    # Add and commit
    git add .
    git commit -m "Deploy API documentation - $(date '+%Y-%m-%d %H:%M:%S')"
    
    echo "✅ Documentation committed to gh-pages branch"
    echo "📋 Next steps:"
    echo "   1. Push the gh-pages branch: git push origin gh-pages"
    echo "   2. Enable GitHub Pages in repository settings"
    echo "   3. Set source to 'gh-pages' branch"
    echo ""
    echo "🌐 Your docs will be available at: https://Job-Portal-Project.github.io/api/"
    echo "📖 Frontend team can access the API specification at the URL above"
    
    # Switch back to main branch
    git checkout 1.x 2>/dev/null || git checkout main 2>/dev/null || git checkout master
else
    echo "📁 Documentation created in $DOCS_DIR/"
    echo "💡 Copy contents to your GitHub Pages repository"
fi

# Cleanup
rm -rf $DOCS_DIR

echo "🎉 Deployment script completed!"