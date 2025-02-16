# PHP Auth Master  

This repository is a user authentication and role management system built with PHP. It provides an easy-to-use admin panel and allows the creation and management of multiple user roles. Security is the top priority in **PHP Auth Master**, ensuring user data is well-protected.  

![Active Sessions](src/images/active_sessions.png)

## Features  

- **Multiple User Roles**: Easily create and manage different roles such as Admin, User, and more.  
- **Admin Panel**: A built-in admin dashboard where user roles and permissions can be managed.  
- **User Authentication**: Secure registration and login with password hashing.  
- **Role-Based Access Control**: Different functionalities and pages are accessible based on user roles.  
- **Automatic Menu Generation**: The admin panel menu is dynamically generated based on user roles.  
- **Advanced Security Measures**: Session tracking, active session management, and automatic logout after a password change.  

---

## 🔐 Security & Active Sessions  

One of the most powerful security features in **PHP Auth Master** is the **Active Session Management System**, which ensures that users can track and control their active sessions.  

### Active Session Features  

- **Session Monitoring**: Users can see a list of all logged-in devices, including:  
  - Device Name  
  - IP Address  
  - Login Time  
  - Last Activity Time  
- **Remove Unauthorized Devices**: Users can manually log out of unwanted or suspicious devices.  
- **Automatic Logout on Password Change**: If a user changes their password, **all active sessions** on other devices will be logged out immediately.  
- **Strong Security**: The system prevents unauthorized access and provides **real-time session tracking**.  

### Example: Active Session Table  

When users navigate to **Active Sessions**, they will see something like this:  

| Device Name | IP Address | Login Time | Last Activity | Action |
|-------------|-----------|------------|--------------|--------|
| Windows PC | 192.168.1.100 | 2025-02-16 10:15:00 | 2025-02-16 10:45:00 | 🔴 Logout |
| iPhone 15 Pro Max  | 203.45.67.89  | 2025-02-16 09:00:00 | 2025-02-16 09:30:00 | 🔴 Logout |

Users can log out of any session with just one click.  

---

## 🛠 Admin Panel  

To use the admin panel, simply define the menu structure. The system will automatically generate the necessary navigation and pages:  

```php
$menuItems = [
    [
        "menuTitle" => "Settings",
        "icon" => "fas fa-cog",
        "pages" => [
            ["title" => "Update Profile", "url" => "index.php"],
            ["title" => "Active Sessions", "url" => "active_sessions.php"]
        ],
    ]
];
```

---

## Preview Pages  

### Login 
![Login](src/images/login.png) 

### Admin Dashboard  
![Admin](src/images/admin_panel.png)  

### User Dashboard  
![User](src/images/user.png)  

---

## 🚀 Setting Up the Project  

Follow these steps to set up the project on your local machine:  

### 1. Clone the Repository  
Run the following command to clone the repository and navigate into the project directory:  

```bash
git clone https://github.com/Iqbolshoh/php-auth-master.git
cd php-auth-master
```

### 2. Import the Database  
Execute the following command to import the database schema into MySQL:  

```bash
mysql -u yourusername -p yourpassword < database.sql
```

Replace `yourusername` and `yourpassword` with your MySQL credentials.  

---

## 🔧 Database Connection and Role Configuration  

To ensure proper database connectivity, update the `config.php` file with the following settings:  

```php
define("DB_SERVER", "localhost");
define("DB_USERNAME", "root");
define("DB_PASSWORD", "");
define("DB_NAME", "auth_master");

const ROLES = [
    'admin' => '/admin/',
    'user' => '/'
];
```

### Explanation:  
- **`DB_SERVER`**: Specifies the database host (default is `localhost`).  
- **`DB_USERNAME`**: Defines the MySQL username (`root` for local setups).  
- **`DB_PASSWORD`**: Specifies the database password (leave empty if using the default local MySQL setup).  
- **`DB_NAME`**: Defines the name of the authentication database (`auth_master`).  
- **`ROLES`**: An array mapping user roles to their corresponding default redirection paths.  

These configurations allow seamless database connectivity and ensure proper user role management.  

---

## 🛡 Why Choose PHP Auth Master?  

✅ **Strong Security** – Active session tracking, password-based session logout, and role-based access.  
✅ **User-Friendly** – Built-in admin panel for easy management.  
✅ **Flexible & Scalable** – Easily add new roles, pages, and users.  
✅ **Real-Time Monitoring** – See who is logged in and remove unauthorized sessions.  

💡 **Ready to secure your PHP application? Start using PHP Auth Master today!** 🚀

---

## Technologies Used

<div style="display: flex; flex-wrap: wrap; gap: 5px;">
    <img src="https://img.shields.io/badge/HTML-%23F06529.svg?style=for-the-badge&logo=html5&logoColor=white"
        alt="HTML">
    <img src="https://img.shields.io/badge/CSS-%231572B6.svg?style=for-the-badge&logo=css3&logoColor=white" alt="CSS">
    <img src="https://img.shields.io/badge/JavaScript-%23323330.svg?style=for-the-badge&logo=javascript&logoColor=%23F7DF1E"
        alt="JavaScript">
     <img src="https://img.shields.io/badge/PHP-%23777BB4.svg?style=for-the-badge&logo=php&logoColor=white" alt="PHP">
   <img src="https://img.shields.io/badge/MySQL-%234479A1.svg?style=for-the-badge&logo=mysql&logoColor=white"
        alt="MySQL">
</div>


## Contributing

Contributions are welcome! If you have suggestions or want to enhance the project, feel free to fork the repository and submit a pull request.


## Connect with Me

I love connecting with new people and exploring new opportunities. Feel free to reach out to me through any of the platforms below:

<table>
    <tr>
        <td>
            <a href="https://github.com/iqbolshoh">
                <img src="https://raw.githubusercontent.com/rahuldkjain/github-profile-readme-generator/master/src/images/icons/Social/github.svg"
                    height="48" width="48" alt="GitHub" />
            </a>
        </td>
        <td>
            <a href="https://t.me/iqbolshoh_777">
                <img src="https://github.com/gayanvoice/github-active-users-monitor/blob/master/public/images/icons/telegram.svg"
                    height="48" width="48" alt="Telegram" />
            </a>
        </td>
        <td>
            <a href="https://www.linkedin.com/in/iiqbolshoh/">
                <img src="https://github.com/gayanvoice/github-active-users-monitor/blob/master/public/images/icons/linkedin.svg"
                    height="48" width="48" alt="LinkedIn" />
            </a>
        </td>
        <td>
            <a href="https://instagram.com/iqbolshoh_777" target="blank"><img align="center"
                    src="https://raw.githubusercontent.com/rahuldkjain/github-profile-readme-generator/master/src/images/icons/Social/instagram.svg"
                    alt="instagram" height="48" width="48" /></a>
        </td>
        <td>
            <a href="https://wa.me/qr/22PVFQSMQQX4F1">
                <img src="https://github.com/gayanvoice/github-active-users-monitor/blob/master/public/images/icons/whatsapp.svg"
                    height="48" width="48" alt="WhatsApp" />
            </a>
        </td>
        <td>
            <a href="https://x.com/iqbolshoh_777">
                <img src="https://img.shields.io/badge/X-000000?style=for-the-badge&logo=x&logoColor=white" height="48"
                    width="48" alt="Twitter" />
            </a>
        </td>
        <td>
            <a href="mailto:iilhomjonov777@gmail.com">
                <img src="https://github.com/gayanvoice/github-active-users-monitor/blob/master/public/images/icons/gmail.svg"
                    height="48" width="48" alt="Email" />
            </a>
        </td>
    </tr>
</table>
