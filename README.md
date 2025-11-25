# 🏋️ Beast Fitness — Full-Stack Fitness Management System

Beast Fitness is a powerful full-stack fitness management web application designed to help users track workouts, set goals, book trainers, manage subscriptions, and access personalized wellness features. A secure Admin Portal is also included for complete system management.

---

## 🚀 Features

### 📝 Workout Journal & Progress Tracking
- Log workouts: sets, reps, weight, distance, duration.
- Track progress toward personal fitness goals.

### 🎯 Goal Management
- Set measurable goals such as weight loss, muscle gain, or endurance.
- Update and monitor progress over time.

### 🧑‍🏫 Trainer Booking System
- View trainer profiles with hourly rates.
- Book secure paid 1:1 training sessions.

### 💎 Premium Subscriptions
- Unlock premium plans: **Pro Member**, **Beast Annual**.
- Access exclusive features and personalized content.

### 🥗 Personalized Diet Plans
- Subscribed users access daily meal plans and diet recommendations.

### 🛡 Admin Portal
Manage:
- Users  
- Trainers  
- Subscription plans  
- System data and overall analytics  

### 📱 Responsive UI
Built with **Tailwind CSS** for seamless desktop and mobile experience.

---

## 🛠️ Technology Stack

| Layer | Technology |
|-------|------------|
| **Frontend** | HTML5, Tailwind CSS |
| **Backend** | PHP 8.2+ (Native PHP), Sessions |
| **Database** | MySQL / MariaDB (PDO) |

---

## 💾 Installation & Setup

Follow these steps to run Beast Fitness locally:

---

### 1. Database Setup (Required for Admin Login)

#### A. Create Database
Create a MySQL database named:

#### B. Import Schema + Dummy Data  
Run the full file:


This includes:
- All tables  
- Foreign keys  
- Dummy data (trainers, subscriptions)  
- Admin user  

#### C. Configure Database Connection  
Edit **conn.php**:

2. Project Structure
```php
beast-fitness/
├── admin/
│   ├── admin_dashboard.php
│   ├── manage_users.php
│   ├── manage_trainers.php
│   └── manage_subscriptions.php
├── conn.php
├── login.php
├── signup.php
├── home.php
├── goals.php
├── trainers.php
|── beast_fitness_db.sql
└── ... other core files
```
3. Accessing the Application
Role	Credentials	Access URL
User	Register via /signup.php	/home.php
Admin	Username: admin
Password: password	/login.php → redirects to Admin Dashboard
🔒 Troubleshooting Admin Login


## ❗ Troubleshooting: Invalid Admin Login

If you see this error message:


Follow the steps below to fix it:

### ✅ 1. Verify `login.php` Checks `is_admin`
Make sure your login logic includes a check similar to:

```php
if ($row['is_admin'] == 1) {
    $_SESSION['is_admin'] = true;
}
```


## 🤝 Contribution and Management

We welcome all new features, bug fixes, optimizations, and documentation improvements.  
To maintain project security and consistency, please follow the guidelines below:

### 🔐 Authentication
All user-level features must verify an active session:
```php
$_SESSION['user_id'];
🛡 Authorization (Admin Only)
```
---
### 🛡 All admin-level features must validate:

$_SESSION['is_admin'] === true;


---
### Screenshots








<img width="1917" height="1021" alt="Screenshot 2025-11-07 180340" src="https://github.com/user-attachments/assets/df6319c8-4fc9-4500-a285-4797efeb1ae9" />
<img width="1919" height="1036" alt="Screenshot 2025-11-07 180527" src="https://github.com/user-attachments/assets/5a7cfc3d-b247-47e8-ba02-62e8e3ba0ab4" />
<img width="1911" height="1036" alt="Screenshot 2025-11-07 180453" src="https://github.com/user-attachments/assets/16664e7c-91b3-4f5f-8a2d-697a168c84bf" />
<img width="1919" height="1013" alt="Screenshot 2025-11-07 180430" src="https://github.com/user-attachments/assets/ad30c6bd-dd54-44eb-8dcb-dd05c9f9d28e" />

