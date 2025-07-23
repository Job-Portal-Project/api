# API Documentation Deployment Guide

This guide explains how to deploy your Laravel API documentation to GitHub Pages for frontend team access.

## 🚀 Quick Start

### 1. Generate Documentation
```bash
php artisan l5-swagger:generate
```

### 2. Deploy to GitHub Pages (Manual)
```bash
./.github/scripts/deploy-docs.sh
git push origin gh-pages
```

### 3. Enable GitHub Pages
1. Go to your repository settings
2. Navigate to "Pages" section
3. Set source to "Deploy from a branch"
4. Select "gh-pages" branch
5. Save settings

Your documentation will be available at: `https://Job-Portal-Project.github.io/api/`

## 🔄 Automatic Deployment

The repository includes a GitHub Actions workflow (`.github/workflows/deploy-api-docs.yml`) that automatically deploys documentation when `storage/api-docs/api-docs.json` is updated.

### Setup Requirements:
1. Enable GitHub Pages in repository settings
2. Grant workflow permissions to write to Pages
3. Push changes to the `1.x` branch

## 📖 Usage

### For Frontend Developers

**Interactive Documentation:**
- Visit: `https://Job-Portal-Project.github.io/api/`
- Interactive Swagger UI with "Try it out" functionality
- Full API endpoint documentation with examples

**OpenAPI JSON Access:**
```javascript
// Fetch the complete OpenAPI specification
const response = await fetch('https://Job-Portal-Project.github.io/api/api-docs.json');
const apiSpec = await response.json();

// Use with OpenAPI generators like openapi-generator or swagger-codegen
// Generate TypeScript/JavaScript client code
// Validate API responses
```

**Raw JSON Viewer:**
- Visit: `https://Job-Portal-Project.github.io/api/json.html`
- Copy/download JSON specification
- Human-readable format

### Integration Examples

**TypeScript Client Generation:**
```bash
# Using openapi-generator
npx @openapitools/openapi-generator-cli generate \
  -i https://Job-Portal-Project.github.io/api/api-docs.json \
  -g typescript-fetch \
  -o ./src/api-client
```

**Validation with API responses:**
```javascript
import { validateSync } from 'class-validator';
import { plainToClass } from 'class-transformer';

// Validate API response against schema
const user = plainToClass(UserDto, apiResponse.data);
const errors = validateSync(user);
```

## 🛠️ Configuration

### Production Environment Variables
Add to your `.env` file:
```env
# API Documentation
L5_SWAGGER_GENERATE_ALWAYS=false  # Set to false in production
L5_SWAGGER_GENERATE_YAML_COPY=false
L5_SWAGGER_OPEN_API_SPEC_VERSION=3.0.0
L5_SWAGGER_CONST_HOST=https://your-api-domain.com
```

### Custom Deployment
To customize the deployment:

1. **Modify HTML templates** in `.github/scripts/deploy-docs.sh`
2. **Update GitHub Actions workflow** in `.github/workflows/deploy-api-docs.yml`
3. **Change documentation styling** by editing the CSS in the generated HTML

## 🌐 Available Endpoints

Once deployed, your GitHub Pages site will have:

- `https://Job-Portal-Project.github.io/api/` - Interactive Swagger UI documentation
- `https://Job-Portal-Project.github.io/api/json.html` - JSON viewer with copy/download
- `https://Job-Portal-Project.github.io/api/api-docs.json` - Raw OpenAPI specification
- `https://Job-Portal-Project.github.io/api/README.md` - Documentation information

## 🔧 Troubleshooting

### Common Issues:

**1. Documentation not updating:**
```bash
# Clear swagger cache and regenerate
rm -rf storage/api-docs/*
php artisan l5-swagger:generate
```

**2. GitHub Pages not enabled:**
- Check repository settings → Pages
- Ensure gh-pages branch exists
- Verify workflow permissions

**3. CORS issues with API calls:**
```php
// Add to routes/api.php for development
Route::options('{any}', function() {
    return response('', 200)
        ->header('Access-Control-Allow-Origin', '*')
        ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
        ->header('Access-Control-Allow-Headers', 'Content-Type, Authorization');
})->where('any', '.*');
```

**4. Local development:**
```bash
# Serve documentation locally
cd temp-docs && python -m http.server 8080
# Visit: http://localhost:8080
```

## 📝 Updating Documentation

When you update your API:

1. **Update OpenAPI annotations** in controllers
2. **Regenerate documentation:**
   ```bash
   php artisan l5-swagger:generate
   ```
3. **Deploy changes:**
   ```bash
   # Automatic (GitHub Actions)
   git add storage/api-docs/api-docs.json
   git commit -m "Update API documentation"
   git push origin 1.x
   
   # Manual
   ./.github/scripts/deploy-docs.sh
   git push origin gh-pages
   ```

## 🎯 Best Practices

1. **Keep documentation up-to-date** with code changes
2. **Use descriptive examples** in OpenAPI annotations  
3. **Include error responses** for all endpoints
4. **Document authentication requirements** clearly
5. **Test documentation** before sharing with frontend team
6. **Version your API** and document breaking changes

## 🔗 Links

- [OpenAPI Specification](https://swagger.io/specification/)
- [Swagger UI Documentation](https://swagger.io/tools/swagger-ui/)
- [L5-Swagger Documentation](https://github.com/DarkaOnLine/L5-Swagger)
- [GitHub Pages Documentation](https://docs.github.com/en/pages)