# GitHub Pages Deployment Setup

## 📍 **Repository Information**

Your API documentation will be deployed to:
- **Repository**: `api` (https://github.com/Job-Portal-Project/api)
- **Organization**: `Job-Portal-Project`
- **Branch**: `gh-pages` (automatically created)
- **URL**: `https://Job-Portal-Project.github.io/api/`

## ✅ **Configuration Details**

- **Admin Access**: ✅ Available (shahmal1yev)
- **Organization**: Job-Portal-Project
- **Repository**: api
- **Deployment Ready**: ✅

## 🔧 **Setup Steps**

### 1. Repository Settings (One-time setup)
You need to enable GitHub Pages in your repository:

1. Go to: `https://github.com/Job-Portal-Project/api/settings/pages`
2. Under "Source", select: **"Deploy from a branch"**
3. Select branch: **"gh-pages"**
4. Click **"Save"**

### 2. Workflow Permissions (One-time setup)
Enable GitHub Actions to deploy Pages:

1. Go to: `https://github.com/Job-Portal-Project/api/settings/actions`
2. Under "Workflow permissions", select: **"Read and write permissions"**
3. Check: **"Allow GitHub Actions to create and approve pull requests"**
4. Click **"Save"**

## 🚀 **Deployment Methods**

### Method 1: Automatic (Recommended)
- **Trigger**: Automatically when `storage/api-docs/api-docs.json` changes
- **Workflow**: `.github/workflows/deploy-api-docs.yml`
- **No manual intervention required**

### Method 2: Manual
```bash
# Generate latest docs
php artisan l5-swagger:generate

# Deploy to gh-pages branch
./.github/scripts/deploy-docs.sh

# Push to GitHub
git push origin gh-pages
```

## 📋 **File Structure**

```
.github/
├── workflows/
│   └── deploy-api-docs.yml     # Automatic deployment workflow
├── scripts/
│   └── deploy-docs.sh          # Manual deployment script
└── DEPLOYMENT-SETUP.md         # This file
```

## 🌐 **Final URLs**

After successful deployment:
- **Interactive Docs**: `https://Job-Portal-Project.github.io/api/`
- **JSON API Spec**: `https://Job-Portal-Project.github.io/api/api-docs.json`
- **JSON Viewer**: `https://Job-Portal-Project.github.io/api/json.html`

## ❓ **Need Help?**

If you encounter issues:
1. Check repository permissions
2. Verify GitHub Pages is enabled
3. Ensure `gh-pages` branch exists
4. Check GitHub Actions logs

## 🔐 **No Additional Credentials Required**

Since we're deploying to the same repository:
- ✅ Uses existing repository permissions
- ✅ GitHub Actions token is automatically provided
- ✅ No external services or tokens needed