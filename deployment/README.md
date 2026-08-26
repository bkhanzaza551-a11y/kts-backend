# Railway Deployment Guide - KTS 10 Pips Bots Backend

## Prerequisites
- GitHub account
- Railway account (railway.app)
- Git installed

## Step 1: Push to GitHub

```bash
cd kts10pipsbots-backend

# Initialize git (if not done)
git init
git add .
git commit -m "Initial commit for Railway deployment"

# Create repo on GitHub, then:
git remote add origin https://github.com/YOUR_USERNAME/kts10pipsbots-backend.git
git branch -M main
git push -u origin main
```

## Step 2: Deploy on Railway

1. Go to [railway.app](https://railway.app)
2. Click **"New Project"**
3. Select **"Deploy from GitHub Repo"**
4. Select your `kts10pipsbots-backend` repo
5. Railway will auto-detect Laravel and start building

## Step 3: Add Environment Variables

In Railway dashboard → Your Service → **Variables** tab → Add:

```
APP_NAME=KTS 10 Pips Bots
APP_ENV=production
APP_DEBUG=false
APP_TIMEZONE=UTC
APP_URL=https://your-app.up.railway.app
DB_CONNECTION=sqlite
SESSION_DRIVER=database
CACHE_STORE=database
QUEUE_CONNECTION=database
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=465
MAIL_USERNAME=ahmedbilalkhangl09@gmail.com
MAIL_PASSWORD=pvrahwujjucsqwlo
MAIL_ENCRYPTION=ssl
MAIL_FROM_ADDRESS=ahmedbilalkhangl09@gmail.com
MAIL_FROM_NAME=KTS 10 Pips Bots
```

**Generate APP_KEY:**
```bash
php artisan key:generate --show
```
Copy the output and add as `APP_KEY=base64:...`

## Step 4: Add Volume for File Storage

1. In Railway dashboard → Your Service → **Settings** tab
2. Scroll to **"Volumes"**
3. Click **"+ New Volume"**
4. Mount Path: `/var/www/storage`
5. Size: 5GB (minimum)

## Step 5: Custom Start Command

In Railway dashboard → Your Service → **Settings** tab → **"Deploy"** section:

Override the start command:
```bash
bash deployment/start.sh
```

## Step 6: Generate APP_KEY via Railway CLI

```bash
# Install Railway CLI
npm i -g @railway/cli

# Login
railway login

# Link to your project
railway link

# Run command
railway run php artisan key:generate
```

## Step 7: Custom Domain (Optional)

1. In Railway dashboard → Your Service → **Settings** tab
2. Scroll to **"Networking"**
3. Click **"Generate Domain"** for free `.up.railway.app` domain
4. Or add custom domain and update DNS

## Step 8: Update Mobile App API URL

Update `src/api/client.js`:
```javascript
const API_BASE = __DEV__
  ? 'http://10.0.2.2:8000/api/v1'
  : 'https://your-app.up.railway.app/api/v1';
```

## Services Running

Railway will run 4 services via Supervisor:

| Service | Command | Purpose |
|---------|---------|---------|
| Nginx | `nginx -g "daemon off;"` | Web server |
| PHP-FPM | `php-fpm8.3 --nodaemonize` | PHP processor |
| Queue Worker | `php artisan queue:work` | Background jobs |
| Scheduler | `php artisan schedule:run` (every 60s) | Cron jobs (signals:track) |

## Troubleshooting

### Check Logs
```bash
railway logs
```

### Run artisan commands
```bash
railway run php artisan migrate
railway run php artisan db:seed
railway run php artisan signals:track
```

### SSH into container
```bash
railway shell
```

### Common Issues

1. **502 Bad Gateway** → App still building, wait 2-3 minutes
2. **Migration errors** → Run `railway run php artisan migrate --force`
3. **Storage permission errors** → Check volume is mounted at `/var/www/storage`
4. **Queue not processing** → Check Supervisor logs: `railway logs`

## Cost Estimate

| Resource | Cost |
|----------|------|
| Starter Plan | $5/mo |
| Volume (5GB) | $5/mo |
| Usage (est.) | $2-5/mo |
| **Total** | **~$12-15/month** |

## Environment Variables Reference

| Variable | Value | Notes |
|----------|-------|-------|
| `APP_ENV` | `production` | Always production on Railway |
| `APP_DEBUG` | `false` | Never true in production |
| `DB_CONNECTION` | `sqlite` | Uses file-based SQLite |
| `SESSION_DRIVER` | `database` | Sessions in SQLite |
| `CACHE_STORE` | `database` | Cache in SQLite |
| `QUEUE_CONNECTION` | `database` | Jobs in SQLite |
| `MAIL_MAILER` | `smtp` | Gmail SMTP |
| `MAIL_PORT` | `465` | SSL port |
| `MAIL_ENCRYPTION` | `ssl` | Required for Gmail |
