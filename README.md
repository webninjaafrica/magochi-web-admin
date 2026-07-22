🚀 Magochi Web Admin
<div align="center">


Complete Web Hosting Control Panel for Headless Linux, SBCs, and DIY IoT Projects

Features • Installation • Documentation • Support

</div>
📋 Overview
Magochi Web Admin is a lightweight, self-contained web hosting control panel designed specifically for headless Linux servers, single-board computers (Raspberry Pi, Orange Pi, etc.), and DIY IoT enthusiasts & software developers. It provides a complete web-based interface for managing web hosting services with minimal resource requirements.

🎯 Target Audience
DIY IoT Hobbyists: Manage your home server, IoT dashboard, or smart home control panels

Software Developers: Test PHP applications, create staging environments, manage microservices

Single-Board Computer Users: Raspberry Pi, Orange Pi, BeagleBone, and similar SBCs

Headless Linux Users: Run a complete web hosting solution without a GUI

Edge Computing: Deploy lightweight hosting on edge devices

🌟 Key Features
Feature	Description
📁 File Manager	Full-featured file browser with drag & drop, edit, and ZIP extraction
🌐 Domain Management	Create, delete, and manage multiple domains with SSL support
🗄️ Database Management	Create and manage MySQL databases with user permissions
📅 Cron Jobs	Schedule automated tasks via URL-based cron
🔑 API System	RESTful API for automation and integration
📦 WordPress Installer	One-click WordPress installation with auto-database setup
🛡️ SSL Management	Upload and manage SSL certificates per domain
🔍 DNS Management	Create and manage DNS records (A, CNAME, MX, etc.)
📊 FTP Users	Create and manage FTP accounts for file access
🎨 Theme System	Customizable colors and dark mode support
📝 Activity Logs	Track all system activities and user actions
⚙️ PHP Settings	Configure PHP.ini settings from web interface
💡 Why Magochi Web Admin?
For DIY IoT & SBC Users
bash
# Run a complete web hosting solution on your Raspberry Pi
# Perfect for home automation dashboards, personal websites, and development
Minimal Resources: Runs on devices with as little as 512MB RAM

No GUI Required: Full web-based administration from any browser

Simple Setup: One-file installation, no complex configurations

Ideal for: Home servers, media centers, IoT gateways, smart home hubs

For Software Developers
Local Development: Create isolated development environments

API Integration: RESTful API for CI/CD pipelines

Testing Environment: Test PHP applications without cloud costs

Microservices: Host multiple services on a single device

📦 Quick Installation
Prerequisites
Linux-based OS (Ubuntu, Debian, Raspberry Pi OS, Armbian)

PHP 7.4+ with extensions: mysqli, zip, curl, mbstring

MySQL/MariaDB 5.7+

Web Server (Apache/Nginx)

One-Click Install
bash
# Download the latest version
wget https://raw.githubusercontent.com/webninjaafrica/magochi-web-admin/main/magochi-web-admin-v3.php

# Or with curl
curl -O https://raw.githubusercontent.com/webninjaafrica/magochi-web-admin/main/magochi-web-admin-v3.php

# Make it executable
chmod +x magochi-web-admin-v3.php

# Run the installer
php magochi-web-admin-v3.php --install
Docker Installation
bash
# Pull and run the container
docker run -d \
  -p 8080:80 \
  -v $(pwd)/data:/var/www/html \
  --name magochi-web-admin \
  webninjaafrica/magochi-web-admin:latest
Manual Installation
Clone the repository

bash
git clone https://github.com/webninjaafrica/magochi-web-admin.git
cd magochi-web-admin
Configure database

sql
CREATE DATABASE magochi_host;
CREATE USER 'magochi_user'@'localhost' IDENTIFIED BY 'your_password';
GRANT ALL PRIVILEGES ON magochi_host.* TO 'magochi_user'@'localhost';
FLUSH PRIVILEGES;
Update configuration

php
// Edit magochi-web-admin-v3.php
$db_host = 'localhost';
$db_user = 'magochi_user';
$db_pass = 'your_password';
$db_name = 'magochi_host';
Set permissions

bash
mkdir -p /var/www/html/{domains,uploads,backups,temp,config}
chmod 755 /var/www/html/{domains,uploads,backups,temp,config}
chown -R www-data:www-data /var/www/html/
Access the panel

text
http://your-server-ip/magochi-web-admin-v3.php
Raspberry Pi Installation Script
bash
#!/bin/bash
# Magochi Web Admin Installer for Raspberry Pi

echo "🚀 Installing Magochi Web Admin on Raspberry Pi"

# Update system
sudo apt update && sudo apt upgrade -y

# Install dependencies
sudo apt install -y apache2 mysql-server php php-mysql php-curl php-zip php-mbstring

# Download the panel
sudo wget -O /var/www/html/index.php https://raw.githubusercontent.com/webninjaafrica/magochi-web-admin/main/magochi-web-admin-v3.php

# Set permissions
sudo chown www-data:www-data /var/www/html/index.php
sudo chmod 644 /var/www/html/index.php

# Restart services
sudo systemctl restart apache2
sudo systemctl restart mysql

echo "✅ Installation complete!"
echo "🌐 Access your panel at: http://$(hostname -I | awk '{print $1}')"
🚀 Quick Start
Default Login
Username: admin

Password: MagochiAdmin2026!

First Steps
Change password:

bash
# Go to Settings > Change Password
Create your first domain:

bash
# Domains > Create Domain
Setup a database:

bash
# Databases > Create Database
Install WordPress:

bash
# Apps & Installers > WordPress > Install
📚 Documentation
API Reference
bash
# List domains
curl -H "X-API-Key: YOUR_API_KEY" \
  "http://your-server/magochi-web-admin-v3.php?api=v1&endpoint=domains"

# Create domain
curl -X POST \
  -H "X-API-Key: YOUR_API_KEY" \
  -d "domain=example.com" \
  "http://your-server/magochi-web-admin-v3.php?api=v1&endpoint=domains"

# Get server info
curl -H "X-API-Key: YOUR_API_KEY" \
  "http://your-server/magochi-web-admin-v3.php?api=v1&endpoint=server"
File Management
bash
# Upload files via command line
curl -F "uploaded_file=@/path/to/file.zip" \
  "http://your-server/magochi-web-admin-v3.php?action=files&path=/home/user"

# Create directory
curl -X POST \
  -d "foldername=my_site" \
  "http://your-server/magochi-web-admin-v3.php?action=files&path=/home/user"
Cron Job Format
text
┌───────────── minute (0 - 59)
│ ┌───────────── hour (0 - 23)
│ │ ┌───────────── day of month (1 - 31)
│ │ │ ┌───────────── month (1 - 12)
│ │ │ │ ┌───────────── day of week (0 - 6) (Sunday to Saturday)
│ │ │ │ │
* * * * * command_to_execute
Common Schedules
Schedule	Cron Expression
Every minute	* * * * *
Every hour	0 * * * *
Daily at 2am	0 2 * * *
Weekly on Sunday	0 0 * * 0
Monthly on 1st	0 0 1 * *
🛠️ Configuration
Directory Structure
text
/var/www/html/
├── magochi-web-admin-v3.php   # Main control panel
├── admin_root/                # Admin root directory
├── domains/                   # Domain folders
│   └── example.com/
│       ├── index.html
│       └── .htaccess
├── uploads/                   # User uploads
├── backups/                   # Database backups
├── temp/                      # Temporary files
└── config/
    └── db_config.php          # Auto-generated config
Environment Variables
bash
# Set these in your .env file or shell
export DB_HOST=localhost
export DB_USER=magochi_user
export DB_PASS=your_password
export DB_NAME=magochi_host
export API_SECRET=your_api_secret
export SERVER_URL=https://your-server.com
Security Recommendations
Change default admin password immediately

Enable HTTPS (Let's Encrypt recommended)

Restrict file permissions:

bash
chmod 640 /var/www/html/magochi-web-admin-v3.php
chown www-data:www-data /var/www/html/magochi-web-admin-v3.php
Use environment variables instead of hardcoded credentials

Enable firewall and restrict access:

bash
sudo ufw allow 80/tcp
sudo ufw allow 443/tcp
sudo ufw enable
🔧 Troubleshooting
Common Issues
MySQL Connection Error

bash
# Start MySQL service
sudo systemctl start mysql
# Or for MariaDB
sudo systemctl start mariadb

# Check if socket exists
ls -la /var/run/mysqld/mysqld.sock
Permission Denied

bash
# Fix file permissions
sudo chown -R www-data:www-data /var/www/html/
sudo chmod -R 755 /var/www/html/
PHP Extensions Missing

bash
# Install required extensions
sudo apt-get install php-mysqli php-zip php-curl php-mbstring php-gd
sudo systemctl restart apache2
WordPress Installation Fails

bash
# Enable PHP extensions
sudo apt-get install php-xml php-curl php-zip
sudo systemctl restart apache2

# Check CURL support
php -m | grep curl
Log Files
bash
# View Apache logs
sudo tail -f /var/log/apache2/error.log

# View MySQL logs
sudo tail -f /var/log/mysql/error.log

# View PHP logs
sudo tail -f /var/log/php_errors.log
🎯 Use Cases
1. Home Automation Dashboard
Host your own Home Assistant or OpenHAB dashboard with domain management and SSL.

2. Development Server
Create isolated development environments for web applications and APIs.

3. IoT Gateway
Manage MQTT brokers, Node-RED dashboards, and edge computing services.

4. Personal Cloud
Host Nextcloud, ownCloud, or Seafile with full control.

5. Testing Environment
CI/CD testing with automated database creation and cleanup.

6. Media Server
Host Plex, Jellyfin, or Emby with integrated web panel.

7. Educational Platform
Create WordPress sites for students or documentation.

🤝 Contributing
We welcome contributions! Here's how you can help:

Development Setup
bash
# Clone the repository
git clone https://github.com/webninjaafrica/magochi-web-admin.git
cd magochi-web-admin

# Install development dependencies
composer install

# Setup testing environment
php -S localhost:8000

# Run tests
./vendor/bin/phpunit
Contribution Guidelines
Fork the repository

Create a feature branch (git checkout -b feature/amazing-feature)

Commit your changes (git commit -m 'Add amazing feature')

Push to branch (git push origin feature/amazing-feature)

Open a Pull Request

Code Style
Follow PSR-12 coding standards

Document functions with PHPDoc

Write unit tests for new features

Keep the single-file approach for simplicity

📊 System Requirements
Minimum Requirements
Component	Requirement
CPU	ARMv6+ or x86_64
RAM	256MB (recommended 512MB)
Storage	100MB + space for sites
OS	Linux (any distribution)
PHP	7.4 or higher
MySQL	5.7 or higher
Recommended for SBCs
Board	Rating	Notes
Raspberry Pi 4	⭐⭐⭐⭐⭐	Best performance
Raspberry Pi 3	⭐⭐⭐⭐	Good for light usage
Orange Pi	⭐⭐⭐⭐	Good alternative
BeagleBone	⭐⭐⭐	Limited resources
Pine64	⭐⭐⭐⭐	Good performance
📄 License
This project is licensed under the MIT License - see the LICENSE file for details.

🙏 Acknowledgments
All contributors and testers

Open-source community

IoT and SBC enthusiasts

💖 Support & Contact
Get in Touch
👤 Developer: Kelvin Magochi
📧 Email: mwangikelvin278@gmail.com · admin@webninjaafrica.com
📱 WhatsApp: +254718265708
🌐 Website: webninjaafrica.com

💰 Support the Project
If you find this project useful, consider supporting its development:

☕ Buy Me a Coffee

💰 PayPal Donation

🪙 Cryptocurrency: Contact for details

📢 Serious Projects & Collaborations
I'm open to:

🚀 Serious Development Projects: Custom hosting solutions, enterprise deployments

🤝 Partnerships: Hosting providers, IoT companies, educational institutions

🔧 Custom Development: Tailored solutions for specific use cases

📚 Training & Consulting: Setup, deployment, and optimization services

📊 Project Status
Metric	Status
Version	10.0
Stability	Stable
Security	Good (recommendations available)
Features	Complete
Documentation	Comprehensive
Support	Active
Roadmap
□ v11.0 - Multi-language support
□ v11.5 - Docker Swarm/Kubernetes support
□ v12.0 - Advanced monitoring and analytics
□ v12.5 - AI-powered security features
□ v13.0 - Cluster management
📌 Quick Links
GitHub Repository

Issue Tracker

Wiki Documentation

Website

<div align="center">
Made with ❤️ by Kelvin Magochi for the DIY, IoT, and open-source community

⭐ Star this project on GitHub ⭐

⬆ Back to Top

</div>
