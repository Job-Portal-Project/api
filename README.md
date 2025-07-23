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

This documentation is automatically deployed via GitHub Actions when `storage/api-docs/api-docs.json` is updated.# Force GitHub Pages rebuild - Wed Jul 23 06:44:15 PM +04 2025
