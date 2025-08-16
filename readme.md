#  Newsletter Subscription System

A **PHP-based newsletter system** with **OTP-based subscription/unsubscription** and a **daily cron job** that sends emails at **9 AM**.  

---

##  Features
-  **Subscribe with OTP verification**  
-  **Unsubscribe with OTP verification**  
-  **Daily newsletter sent at 9 AM (via cron)**  
-  **Secure OTP**  

---

##  Tech Stack
- **Backend:** PHP  
- **Frontend:** HTML + CSS + PHP
- **Mailer:** PHPMailer  
- **Scheduler:** Cron job (Linux crontab)  

---

##  Project Structure
```
src/
 ├── cron.php               # Function run in the cron job
 ├── index.php              # Handles subscription & OTP generation
 ├── unsubscribe.php        # Handles unsubscription & OTP generation
 ├── cron.php               # Runs daily at 9 AM, sends newsletter
 ├── functions.php          # Common utility functions (OTP, DB, Mail)
 ├── style.css              # style for the ui
 └── registered_emails.txt  # To store Emails     
```

---

##  Workflow

###  Subscription
1. User enters email → `subscribe.php`  
2. OTP generated & sent to email → User enters OTP 
3. On success → email stored in text file  

###  Unsubscription
1. User enters email → `unsubscribe.php`  
2. OTP generated & sent to email → User enters OTP 
3. On success → email stored in text file    

###  Cron Job (Daily Newsletter)
1. Cron triggers `cron.php` every day at **9:00 AM**  
2. Fetch subscriber list from DB  
3. Generate newsletter content (XKCD comic)  
4. Send email to all subscribers  

---

##  Cron Job Setup
Edit crontab with:  

# Add this line to run the job daily at **9 AM**:  
```bash
0 9 * * * /usr/bin/php /path/to/project/src/cron.php
```

---

##  Email Types
- **Subscription OTP:** “Your OTP is 123456. Enter this to confirm subscription.”  
- **Unsubscription OTP:** “Your OTP is 987654. Enter this to confirm unsubscription.”  
- **Daily Newsletter:** “Here’s today’s newsletter/comic/article.”  


