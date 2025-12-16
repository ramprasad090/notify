# Real-Time Notification System

A complete real-time messaging system built with Laravel 12, featuring WebSocket broadcasting via Pusher, database queue processing, and a modern, aesthetic dashboard with visual message flow tracking.

## 🎯 Project Overview

This system demonstrates a production-ready implementation of real-time notifications where:
- Users send messages via a REST API
- Messages are queued for background processing
- Queue workers process and sanitize messages
- Processed messages are broadcast via WebSocket to all connected clients
- UI shows real-time message flow from Pending → Processed

## ✨ Features

### Core Functionality
- **REST API**: Create and retrieve messages via JSON endpoints
- **Queue System**: Background message processing with Laravel Database Queue
- **Real-Time Broadcasting**: Instant WebSocket delivery via Pusher
- **Message Processing**: Automatic sanitization and metadata appending
- **Persistent Storage**: SQLite database with optimized indexes

### User Interface
- **Two-Column Layout**: Pending messages (right) → Processed messages (left)
- **Live Statistics**: Real-time counts for total, pending, and processed messages
- **Visual Flow Indicator**: Shows message lifecycle stages
- **Status Animations**: Spinning loader for pending, checkmark for processed
- **Color-Coded States**: Yellow (pending) and green (processed) themes
- **Responsive Design**: Works on desktop and mobile devices
- **Modern Aesthetic**: Clean design with no gradients, subtle shadows

### Technical Features
- **Non-Blocking API**: Immediate response without waiting for processing
- **XSS Protection**: HTML sanitization and escaping
- **Input Validation**: Server-side validation with detailed error messages
- **Error Handling**: Failed job logging and retry mechanisms
- **Scalable Architecture**: Easy to add multiple queue workers

## 🏗️ System Architecture

### Message Flow

```
┌──────────────┐
│ User submits │
│   message    │
└──────┬───────┘
       │
       ▼
┌──────────────────────────┐
│  POST /api/messages      │
│  1. Validate             │
│  2. Store (pending)      │
│  3. Dispatch to queue    │
│  4. Return immediately   │
└──────┬───────────────────┘
       │
       ▼
┌──────────────────────────┐
│  Queue (jobs table)      │
│  - Job stored            │
│  - Status: pending       │
└──────┬───────────────────┘
       │
       ▼
┌──────────────────────────┐
│  Queue Worker            │
│  1. Pick up job          │
│  2. Sanitize message     │
│  3. Append metadata      │
│  4. Update database      │
│  5. Broadcast event      │
└──────┬───────────────────┘
       │
       ▼
┌──────────────────────────┐
│  Pusher WebSocket        │
│  Channel: messages       │
│  Event: message.received │
└──────┬───────────────────┘
       │
       ▼
┌──────────────────────────┐
│  All Connected Clients   │
│  - Receive update        │
│  - Move to "Processed"   │
└──────────────────────────┘
```

### Queue Processing Details

**Queue Driver**: Database (SQLite)
- Messages are queued in the `jobs` table
- Queue workers poll and process jobs continuously
- Failed jobs are logged in `failed_jobs` table
- Supports retry mechanisms and job chaining

**Processing Steps**:
1. **Sanitization**: `strip_tags()` removes HTML tags
2. **Escaping**: `htmlspecialchars()` prevents XSS
3. **Metadata**: Timestamp appended to message
4. **Database Update**: Mark as processed
5. **Broadcasting**: Pusher WebSocket event triggered

## 🛠️ Tech Stack

| Component | Technology | Purpose |
|-----------|-----------|---------|
| **Backend** | Laravel 12 | PHP framework |
| **Database** | SQLite | Message & queue storage |
| **Queue** | Database Driver | Background job processing |
| **Broadcasting** | Pusher | WebSocket real-time updates |
| **Frontend** | Vanilla JavaScript | Dashboard UI |
| **CSS** | Custom CSS | Modern, clean design |

## 📋 Prerequisites

Before you begin, ensure you have:

- **PHP 8.2 or higher** with extensions:
  - `intl` (Internationalization)
  - `pdo_sqlite`
  - `mbstring`
  - `openssl`
- **Composer** (PHP dependency manager)
- **Node.js 16+** and npm
- **Pusher Account** (free tier available at [pusher.com](https://pusher.com))
- **Git** (for version control)

### Check PHP Extensions

```bash
# List all PHP extensions
php -m

# Check specific extensions
php -m | grep -E "intl|pdo_sqlite|mbstring"
```

### Enable Missing Extensions (macOS with Homebrew)

```bash
# Find php.ini location
php --ini

# Edit php.ini (usually at /opt/homebrew/etc/php/8.x/php.ini)
nano /opt/homebrew/etc/php/8.4/php.ini

# Uncomment these lines (remove semicolon):
extension=intl
extension=pdo_sqlite
extension=mbstring
```

## 🚀 Installation Guide

### Step 1: Clone the Repository

```bash
git clone <repository-url>
cd notify
```

### Step 2: Install Dependencies

```bash
# Install PHP dependencies
composer install

# Install Node.js dependencies
npm install
```

### Step 3: Environment Configuration

```bash
# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate
```

### Step 4: Configure Pusher

1. **Sign up for Pusher**:
   - Go to [pusher.com](https://pusher.com)
   - Create a free account
   - Create a new app/channel

2. **Get your credentials**:
   - App ID
   - Key
   - Secret
   - Cluster (e.g., `us2`, `eu`, `ap1`)

3. **Update `.env` file**:

```env
# Broadcasting
BROADCAST_CONNECTION=pusher

# Queue
QUEUE_CONNECTION=database

# Pusher Configuration
PUSHER_APP_ID=your_app_id_here
PUSHER_APP_KEY=your_app_key_here
PUSHER_APP_SECRET=your_app_secret_here
PUSHER_APP_CLUSTER=your_cluster_here

# Frontend Pusher Config
VITE_PUSHER_APP_KEY="${PUSHER_APP_KEY}"
VITE_PUSHER_APP_CLUSTER="${PUSHER_APP_CLUSTER}"
```

### Step 5: Database Setup

```bash
# Create SQLite database file (if not exists)
touch database/database.sqlite

# Run migrations
php artisan migrate

# Verify migrations
php artisan migrate:status
```

### Step 6: Build Frontend Assets

```bash
# Production build
npm run build

# OR for development with hot reload
npm run dev
```

## 🎮 Running the Application

### Development Mode

You need **THREE terminal windows** running simultaneously:

#### Terminal 1: Laravel Server
```bash
php artisan serve
```
Server will start at: http://localhost:8000

#### Terminal 2: Queue Worker
```bash
php artisan queue:work --tries=3
```
This processes messages in the background

#### Terminal 3: Frontend Assets (Optional)
```bash
npm run dev
```
Only needed if you're making UI changes

### Production Mode

For production deployment:

```bash
# Build assets
npm run build

# Use supervisor for queue workers (see below)
```

#### Supervisor Configuration

Create `/etc/supervisor/conf.d/notify-queue.conf`:

```ini
[program:notify-queue]
process_name=%(program_name)s_%(process_num)02d
command=php /path/to/notify/artisan queue:work database --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=4
redirect_stderr=true
stdout_logfile=/path/to/notify/storage/logs/worker.log
stopwaitsecs=3600
```

Start supervisor:
```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start notify-queue:*
```

## 📡 API Documentation

### Base URL
```
http://localhost:8000/api
```

### Endpoints

#### 1. Create Message

**Endpoint**: `POST /api/messages`

**Request Headers**:
```
Content-Type: application/json
```

**Request Body**:
```json
{
  "sender_id": 123,
  "message": "Hello, this is a test message!"
}
```

**Validation Rules**:
- `sender_id`: Required, integer, minimum value 1
- `message`: Required, string, maximum 5000 characters

**Success Response** (201 Created):
```json
{
  "success": true,
  "message_id": 1,
  "data": {
    "id": 1,
    "sender_id": 123,
    "message": "Hello, this is a test message!",
    "created_at": "2025-12-16T10:30:00.000000Z"
  }
}
```

**Error Response** (422 Unprocessable Entity):
```json
{
  "success": false,
  "errors": {
    "sender_id": [
      "The sender id field is required."
    ],
    "message": [
      "The message field is required."
    ]
  }
}
```

**cURL Example**:
```bash
curl -X POST http://localhost:8000/api/messages \
  -H "Content-Type: application/json" \
  -d '{
    "sender_id": 123,
    "message": "Hello, World!"
  }'
```

#### 2. Get Messages

**Endpoint**: `GET /api/messages`

**Response** (200 OK):
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "sender_id": 123,
      "message": "Hello, World!",
      "processed_message": "Hello, World! [Processed at: 2025-12-16 10:30:01]",
      "is_processed": true,
      "created_at": "2025-12-16T10:30:00.000000Z",
      "updated_at": "2025-12-16T10:30:01.000000Z"
    }
  ]
}
```

**cURL Example**:
```bash
curl http://localhost:8000/api/messages
```

## 🔌 WebSocket Events

### Channel: `messages`

**Event Name**: `message.received`

**Event Payload**:
```json
{
  "id": 1,
  "sender_id": 123,
  "message": "Hello, World! [Processed at: 2025-12-16 10:30:01]",
  "is_processed": true,
  "created_at": "2025-12-16T10:30:00.000000Z"
}
```

### Frontend Implementation

The dashboard automatically connects to Pusher and listens for events:

```javascript
// Initialize Pusher
const pusher = new Pusher(PUSHER_KEY, {
    cluster: PUSHER_CLUSTER,
    forceTLS: true
});

// Subscribe to channel
const channel = pusher.subscribe('messages');

// Listen for events
channel.bind('message.received', (data) => {
    // Handle processed message
    console.log('New message received:', data);
});
```

## 🗄️ Database Schema

### Messages Table

```sql
CREATE TABLE messages (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    sender_id BIGINT UNSIGNED NOT NULL,
    message TEXT NOT NULL,
    processed_message TEXT NULL,
    is_processed BOOLEAN DEFAULT 0,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    INDEX idx_sender_id (sender_id),
    INDEX idx_created_at (created_at)
);
```

**Fields**:
- `id`: Unique message identifier
- `sender_id`: User/sender identifier
- `message`: Original message text
- `processed_message`: Sanitized message with metadata
- `is_processed`: Processing status (0=pending, 1=processed)
- `created_at`: Message creation timestamp
- `updated_at`: Last update timestamp

### Jobs Table (Queue)

```sql
CREATE TABLE jobs (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    queue VARCHAR(255) NOT NULL,
    payload LONGTEXT NOT NULL,
    attempts TINYINT UNSIGNED NOT NULL,
    reserved_at INT UNSIGNED NULL,
    available_at INT UNSIGNED NOT NULL,
    created_at INT UNSIGNED NOT NULL,
    INDEX idx_queue_reserved_at (queue, reserved_at)
);
```

## 🧪 Testing the System

### Manual Testing

1. **Open the dashboard**: http://localhost:8000

2. **Verify connection**: Status should show "Connected" (green dot)

3. **Send a message**:
   - Enter Sender ID: `1`
   - Enter Message: `Hello, World!`
   - Click "Send Message"

4. **Observe the flow**:
   - Message appears in "Pending" (right side) with spinner
   - After ~1 second, moves to "Processed" (left side) with checkmark
   - Statistics update in real-time

5. **Test real-time sync**:
   - Open dashboard in another browser tab
   - Send a message from one tab
   - See it appear in both tabs simultaneously

### API Testing with cURL

```bash
# Send a message
curl -X POST http://localhost:8000/api/messages \
  -H "Content-Type: application/json" \
  -d '{"sender_id": 1, "message": "Test message via cURL"}'

# Get all messages
curl http://localhost:8000/api/messages

# Get messages with pretty print
curl http://localhost:8000/api/messages | json_pp
```

### Queue Testing

```bash
# View queue jobs
php artisan queue:work --once

# Monitor queue in real-time
php artisan queue:monitor

# View failed jobs
php artisan queue:failed

# Retry all failed jobs
php artisan queue:retry all

# Clear all failed jobs
php artisan queue:flush
```

### Database Testing

```bash
# Open Laravel Tinker
php artisan tinker

# Query messages
>>> \App\Models\Message::count()
>>> \App\Models\Message::where('is_processed', true)->get()
>>> \App\Models\Message::latest()->first()

# Clear all messages (for testing)
>>> \App\Models\Message::truncate()
```

## 🔧 Common Commands

### Laravel Artisan

```bash
# View routes
php artisan route:list

# Clear caches
php artisan cache:clear
php artisan config:clear
php artisan view:clear

# Run migrations
php artisan migrate
php artisan migrate:fresh
php artisan migrate:status

# Queue management
php artisan queue:work
php artisan queue:restart
php artisan queue:failed

# Database inspection
php artisan db:show
php artisan db:table messages
```

### Git Workflow

```bash
# View current status
git status

# View commits
git log --oneline

# View branches
git branch

# Current implementation is on:
git checkout feature/real-time-notification-system

# Create a remote repository and push
git remote add origin <your-repo-url>
git push -u origin feature/real-time-notification-system
```

## 🐛 Troubleshooting

### Messages Not Appearing in Real-Time

**Problem**: Messages stay in pending or don't move to processed

**Solutions**:
1. Check queue worker is running:
   ```bash
   php artisan queue:work
   ```

2. Verify Pusher credentials in `.env`:
   ```bash
   grep PUSHER .env
   ```

3. Check browser console for errors (F12)

4. Verify broadcast connection:
   ```bash
   grep BROADCAST_CONNECTION .env
   # Should be: BROADCAST_CONNECTION=pusher
   ```

### Queue Jobs Not Processing

**Problem**: Jobs stuck in queue

**Solutions**:
1. Check failed jobs:
   ```bash
   php artisan queue:failed
   ```

2. View detailed job errors:
   ```bash
   tail -f storage/logs/laravel.log
   ```

3. Restart queue worker:
   ```bash
   php artisan queue:restart
   php artisan queue:work
   ```

4. Check database permissions:
   ```bash
   ls -la database/database.sqlite
   ```

### Pusher Connection Errors

**Problem**: WebSocket disconnected or not connecting

**Solutions**:
1. Verify Pusher credentials are correct

2. Check Pusher dashboard for connection logs

3. Ensure `.env` variables are loaded:
   ```bash
   php artisan config:clear
   ```

4. Verify Pusher library is installed:
   ```bash
   composer show pusher/pusher-php-server
   ```

### PHP Extension Missing

**Problem**: `intl` extension not found

**Solutions**:
1. Check if extension exists:
   ```bash
   php -m | grep intl
   ```

2. Enable in php.ini:
   ```bash
   # Find php.ini
   php --ini

   # Edit and uncomment
   nano /opt/homebrew/etc/php/8.4/php.ini
   # Change: ;extension=intl
   # To: extension=intl
   ```

3. Restart PHP:
   ```bash
   brew services restart php@8.4
   ```

### Database Errors

**Problem**: Migration or query errors

**Solutions**:
1. Ensure database file exists:
   ```bash
   touch database/database.sqlite
   chmod 664 database/database.sqlite
   ```

2. Run migrations:
   ```bash
   php artisan migrate:fresh
   ```

3. Check database connection:
   ```bash
   php artisan db:monitor
   ```

## 🔄 Alternative Configurations

### Using Redis Queue

1. **Install Redis**:
   ```bash
   brew install redis
   brew services start redis
   ```

2. **Install PHP Redis extension**:
   ```bash
   pecl install redis
   ```

3. **Update `.env`**:
   ```env
   QUEUE_CONNECTION=redis
   REDIS_HOST=127.0.0.1
   REDIS_PASSWORD=null
   REDIS_PORT=6379
   ```

4. **Restart queue worker**:
   ```bash
   php artisan queue:restart
   php artisan queue:work redis
   ```

### Using MySQL Database

1. **Update `.env`**:
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=notify_db
   DB_USERNAME=root
   DB_PASSWORD=your_password
   ```

2. **Create database**:
   ```sql
   CREATE DATABASE notify_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   ```

3. **Run migrations**:
   ```bash
   php artisan migrate:fresh
   ```

### Using Laravel Reverb (Self-Hosted WebSocket)

1. **Install Reverb**:
   ```bash
   composer require laravel/reverb
   php artisan reverb:install
   ```

2. **Update `.env`**:
   ```env
   BROADCAST_CONNECTION=reverb
   REVERB_APP_ID=your_app_id
   REVERB_APP_KEY=your_app_key
   REVERB_APP_SECRET=your_app_secret
   ```

3. **Start Reverb server**:
   ```bash
   php artisan reverb:start
   ```

4. **Update frontend** ([dashboard.blade.php](resources/views/dashboard.blade.php)):
   ```javascript
   // Replace Pusher with Reverb client
   import Echo from 'laravel-echo';
   import Pusher from 'pusher-js';

   window.Echo = new Echo({
       broadcaster: 'reverb',
       key: REVERB_KEY,
       wsHost: window.location.hostname,
       wsPort: 8080,
       forceTLS: false,
       enabledTransports: ['ws', 'wss'],
   });
   ```

## 📁 Project Structure

```
notify/
├── app/
│   ├── Events/
│   │   └── MessageReceived.php       # WebSocket broadcast event
│   ├── Http/
│   │   └── Controllers/
│   │       └── MessageController.php  # API endpoints
│   ├── Jobs/
│   │   └── ProcessMessage.php        # Queue job for processing
│   └── Models/
│       └── Message.php               # Eloquent model
├── config/
│   ├── broadcasting.php              # Pusher configuration
│   └── queue.php                     # Queue configuration
├── database/
│   ├── migrations/
│   │   └── *_create_messages_table.php
│   └── database.sqlite               # SQLite database file
├── resources/
│   └── views/
│       └── dashboard.blade.php       # Modern UI dashboard
├── routes/
│   ├── api.php                       # API routes
│   ├── channels.php                  # Broadcast channels
│   └── web.php                       # Web routes
├── .env                              # Environment configuration
├── composer.json                     # PHP dependencies
├── package.json                      # Node.js dependencies
└── README.md                         # This file
```

## 🔒 Security Features

- **Input Validation**: Server-side validation on all API inputs
- **XSS Prevention**: HTML tags stripped and special characters escaped
- **CSRF Protection**: Enabled on all web forms
- **SQL Injection Protection**: Eloquent ORM with parameter binding
- **Sanitization**: Messages sanitized before broadcasting

## 📈 Performance Optimization

- **Database Indexing**: Indexes on `sender_id` and `created_at`
- **Query Limits**: API returns max 100 messages
- **Queue Workers**: Supports multiple workers for parallel processing
- **Connection Pooling**: Persistent WebSocket connections
- **Asset Optimization**: Minified CSS/JS in production

