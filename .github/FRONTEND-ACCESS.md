# Frontend Team API Documentation Access

## 🌐 **Live Documentation URLs**

Once deployed to GitHub Pages, your frontend team can access:

### **Interactive Documentation**
- **URL**: https://Job-Portal-Project.github.io/api/
- **Features**: 
  - Interactive Swagger UI
  - "Try it out" functionality
  - JWT Bearer token authentication
  - Complete endpoint documentation

### **OpenAPI JSON Specification**
- **URL**: https://Job-Portal-Project.github.io/api/api-docs.json
- **Use Cases**:
  - Client code generation
  - API schema validation
  - Documentation tools integration

### **JSON Viewer & Download**
- **URL**: https://Job-Portal-Project.github.io/api/json.html
- **Features**:
  - Human-readable JSON format
  - Copy to clipboard functionality
  - Direct download option

## 💻 **Frontend Integration Examples**

### **Fetch API Specification**
```javascript
// Fetch the complete OpenAPI spec
const response = await fetch('https://Job-Portal-Project.github.io/api/api-docs.json');
const apiSpec = await response.json();

console.log('API Title:', apiSpec.info.title);
console.log('API Version:', apiSpec.info.version);
console.log('Available endpoints:', Object.keys(apiSpec.paths));
```

### **Generate TypeScript Client**
```bash
# Using openapi-generator-cli
npx @openapitools/openapi-generator-cli generate \
  -i https://Job-Portal-Project.github.io/api/api-docs.json \
  -g typescript-fetch \
  -o ./src/api-client \
  --additional-properties=typescriptThreePlus=true
```

### **Generate Axios Client**
```bash
# Using openapi-generator-cli for Axios
npx @openapitools/openapi-generator-cli generate \
  -i https://Job-Portal-Project.github.io/api/api-docs.json \
  -g typescript-axios \
  -o ./src/api-client
```

### **React Query Integration**
```typescript
import { useQuery } from '@tanstack/react-query';

const useApiSpec = () => {
  return useQuery({
    queryKey: ['apiSpec'],
    queryFn: async () => {
      const response = await fetch('https://Job-Portal-Project.github.io/api/api-docs.json');
      return response.json();
    },
    staleTime: 1000 * 60 * 60, // 1 hour
  });
};
```

## 🔐 **Authentication Integration**

The API uses JWT Bearer tokens. Here's how to integrate:

### **Token Usage Example**
```javascript
// Example API call with JWT token
const token = 'your-jwt-access-token';

const response = await fetch('https://your-api-domain.com/api/v1/auth/me', {
  method: 'GET',
  headers: {
    'Authorization': `Bearer ${token}`,
    'Content-Type': 'application/json',
    'Accept': 'application/json'
  }
});

const userData = await response.json();
```

### **Token Types**
- **Access Token**: For API endpoints (expires in ~15 minutes)
- **Refresh Token**: For obtaining new access tokens (expires in ~7 days)

## 📋 **Available Endpoints**

Based on the OpenAPI specification:

### **Authentication**
- `POST /api/v1/auth/register` - Register new user
- `POST /api/v1/auth/authenticate` - User login
- `GET /api/v1/auth/me` - Get user profile (requires access token)
- `POST /api/v1/auth/refresh` - Refresh tokens (requires refresh token)
- `DELETE /api/v1/auth/revoke` - Logout/revoke tokens (requires access token)

## 🔄 **Documentation Updates**

The documentation is automatically updated when the API changes:

1. **Automatic Updates**: Documentation rebuilds when API code changes
2. **Real-time Access**: Always reflects the latest API version
3. **No Cache Issues**: Fresh documentation on every deployment

## 📞 **Support**

If you encounter issues with the API documentation:

1. **Check Status**: Verify the GitHub Pages site is accessible
2. **API Questions**: Contact the backend team
3. **Integration Help**: Reference the OpenAPI specification for exact schemas

## 🛠️ **Development Workflow**

1. **Development**: Use local API at `http://localhost:8000/api/documentation`
2. **Staging**: Use staging API documentation (if available)
3. **Production**: Use GitHub Pages documentation at the URLs above

---

**Last Updated**: Auto-generated from OpenAPI specification  
**Repository**: https://github.com/Job-Portal-Project/api