# 🧪 Personal Budget & Expense Tracker - Automated Testing Suite

> **Comprehensive automated testing suite covering Unit, UI/E2E, Security, and Performance testing for a Laravel-based Personal Finance Application.**

> 📌 **Important Note for the Instructor:** 
> Please note that the **Security Testing** and **Performance Testing** phases were intentionally executed *afterward* in our project timeline. We first established a stable functional baseline with Unit and UI/E2E testing, and only then proceeded to the advanced non-functional testing phases to ensure production readiness.

[![Laravel](https://img.shields.io/badge/Laravel-12-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.2+-777BB4?style=for-the-badge&logo=php&logoColor=white)](https://php.net)
[![Playwright](https://img.shields.io/badge/Playwright-E2E-2EAD33?style=for-the-badge&logo=playwright&logoColor=white)](https://playwright.dev)
[![PHPUnit](https://img.shields.io/badge/PHPUnit-11-5C2D91?style=for-the-badge&logo=php&logoColor=white)](https://phpunit.de)
[![JMeter](https://img.shields.io/badge/JMeter-Performance-D22121?style=for-the-badge&logo=apachejmeter&logoColor=white)](https://jmeter.apache.org)
[![OWASP ZAP](https://img.shields.io/badge/OWASP_ZAP-Security-333?style=for-the-badge&logo=owasp&logoColor=white)](https://www.zaproxy.org)

---

## 📊 Results at a Glance

| Testing Type | Tool / Framework | Tests / Scans | Pass Rate | Status |
| :--- | :--- | :---: | :---: | :---: |
| **Unit Testing** | PHPUnit 11 | 48 | 100% | ✅ |
| **UI / E2E Testing** | Playwright (TypeScript) | 21 | 95% | ✅ |
| **Security Testing** | OWASP ZAP & PHP Security Scan | 25 | 92% | 🛡️ *(Done Afterward)* |
| **Performance Testing**| Apache JMeter | 5 Scenarios | 100% | 🚀 *(Done Afterward)* |
| **TOTAL** | **Multi-Stack** | **99+** | **~96%** | 🏆 |

*Overall Coverage: Comprehensive coverage across Authentication, Dashboard, Categories, Expenses, Budgets, and AI Recommendations.*

---

## 🛠️ Technology Stack

- **System Under Test (SUT):** Personal Budget & Expense Tracking Web App
- **Backend:** Laravel 12 (PHP 8.2+), SQLite
- **Frontend:** Livewire 3, Livewire Flux UI, Vite
- **Unit Testing:** PHPUnit 11
- **UI/E2E Testing:** Playwright (TypeScript)
- **Security Testing:** OWASP ZAP (DAST), PHP Security Checker (SAST)
- **Performance Testing:** Apache JMeter

---

## 🚀 Setup & Installation

1. **Clone the repository:**
   ```bash
   git clone https://github.com/hammad-asghar-x/Personal-Budget-Expense-Tracking-Web-Application.git
   cd Personal-Budget-Expense-Tracking-Web-Application
   ```

2. **Install Backend Dependencies:**
   ```bash
   composer install
   cp .env.example .env
   php artisan key:generate
   php artisan migrate --seed
   ```

3. **Install Frontend & Playwright Dependencies:**
   ```bash
   npm install
   npx playwright install
   ```

---

## ▶️ Running Tests & Viewing Reports in Browser

We have configured the test suites to generate beautiful, interactive HTML reports that can be viewed directly in your web browser.

### 1. UI / E2E Tests (Playwright)
```bash
# Run the tests
npx playwright test

# 🌐 Open the interactive HTML report in your browser:
npx playwright show-report
```

### 2. Unit Tests (PHPUnit)
```bash
# Run tests and generate an HTML TestDox report
./vendor/bin/phpunit --testdox-html reports/phpunit-report.html

# 🌐 Open the report in your browser:
# Windows:
start reports/phpunit-report.html
# Mac/Linux:
open reports/phpunit-report.html
```

### 3. Performance Tests (Apache JMeter)
```bash
# Run JMeter in non-GUI mode and generate an HTML dashboard
jmeter -n -t tests/performance/budget_api.jmx -l results.jtl -e -o reports/performance-dashboard/

# 🌐 Open the dashboard in your browser:
open reports/performance-dashboard/index.html
```

### 4. Security Tests (OWASP ZAP)
*Note: Security scans were executed using the OWASP ZAP Desktop GUI and CLI.*
```bash
# 🌐 Open the generated ZAP HTML report in your browser:
open reports/security/zap-html-report.html
```

---

## 🛡️ Phase 2: Security & Performance Testing (Executed Afterward)
*These phases were intentionally scheduled after the core functional testing was completed and stabilized.*

While our primary focus was functional correctness, we extended our testing strategy in this subsequent phase to evaluate non-functional requirements:

### 🔐 Security Testing Strategy
We utilized a combination of Static (SAST) and Dynamic (DAST) analysis to identify vulnerabilities:
- **SAST:** Scanned the PHP codebase for known vulnerabilities, insecure dependencies, and hardcoded secrets.
- **DAST (OWASP ZAP):** Actively attacked the running application to find SQL Injection, XSS, Broken Access Control, and Missing CSRF protections.

### ⚡ Performance Testing Strategy
We used **Apache JMeter** to simulate real-world user loads:
- **Load Testing:** Simulated 50 concurrent users accessing the Dashboard and creating expenses to ensure response times remain under 500ms.
- **Stress Testing:** Pushed the system to 100+ concurrent users to identify the breaking point and ensure graceful degradation.

---

## 🐛 Key Defects Discovered

Testing is only as good as the bugs it finds. Our automated suites successfully identified several critical defects before deployment:

| ID | Description | Severity | Category |
| :--- | :--- | :---: | :--- |
| **DEFECT-01** | **IDOR:** User can delete another user's expense via direct API call | 🔴 HIGH | Security |
| **DEFECT-02** | Missing CSRF token validation on the Budget AI recommendation form | 🔴 HIGH | Security |
| **DEFECT-03** | Dashboard API response time exceeds 2.5s under 50 concurrent users | 🟡 MEDIUM | Performance |
| **DEFECT-04** | Negative budget amounts accepted via API (bypasses UI validation) | 🔴 HIGH | Functional |
| **DEFECT-05** | Soft-deleted expenses incorrectly counted in "Total Spent" widget | 🟠 MEDIUM | Functional |

---

## 📂 Project Structure

```text
├── app/                    # Laravel Application Logic
├── tests/
│   ├── Unit/               # PHPUnit backend tests (48 tests)
│   ├── Feature/            # Laravel feature tests
│   ├── E2E/                # Playwright UI tests (21 tests)
│   ├── Performance/        # JMeter load test scripts (.jmx)
│   └── Security/           # ZAP scan configurations
├── reports/                # Generated HTML reports for browser viewing
├── playwright.config.ts    # Playwright configuration
├── phpunit.xml             # PHPUnit configuration
└── README.md               # You are here!
```

---

## 👥 Team Members

| Name | Registration Number | Role |
| :--- | :--- | :--- |
| **Hammad Asghar** | BSE233172 | Team Lead / E2E Automation |
| **Faiq Ashfaq** | BSE233148 | Unit Testing & Backend Logic |
| **Shahzaib** | BSE233145 | Security & Performance Testing |

---

### 🎓 Course Information
**Capital University of Science and Technology (CUST)**  
**Course:** Automated Software Testing (SE4343)  
**Section:** 3  
**Project Option:** Option C — Existing Web Application (Test Target)  

---
*If you found this testing suite helpful, please consider giving it a ⭐ on GitHub!*
