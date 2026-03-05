# Studly 🎓
[![Symfony](https://img.shields.io/badge/Symfony-6.4-black?logo=symfony)](https://symfony.com/)
[![PHPStan](https://img.shields.io/badge/PHPStan-Level%20Max-brightgreen)](https://phpstan.org/)
[![License](https://img.shields.io/badge/License-Proprietary-blue)](LICENSE)

**Studly** is a premium, feature-rich platform designed to optimize student collaboration and task management. It combines traditional academic management with modern AI-driven insights, featuring a robust microservices architecture and state-of-the-art security practices.

---

## 🚀 Key Features

- **Smart Group Management**: Create, search, and join study groups with dynamic capacity and invitation systems.
- **AI Task Assistant**: Leverage AI to analyze task descriptions and provide optimized planning suggestions.
- **Integrated Calendar**: Seamlessly manage deadlines and events across projects and groups.
- **Score & Gamification**: Stay motivated with a points-based system that rewards task completion and group participation.
- **Face Authentication**: Advanced security options using face embedding microservices (Python-based).
- **Google Integration**: Single Sign-On (SSO) and calendar synchronization with Google apis.
- **Automated Recommendations**: Tailored suggestions for courses and projects based on user state and performance.

---

## 🛠️ Technical Stack

- **Backend**: [Symfony 6.4 LTS](https://symfony.com/), [PHP 8.2+](https://www.php.net/)
- **Database**: [MySQL 8.0](https://www.mysql.com/), [Doctrine ORM 3](https://www.doctrine-project.org/)
- **Microservices**: 
    - Python-based **Face Service** for biometric analysis.
    - AI-driven **Recommendation Engine**.
- **Security**: Google OAuth 2.0, custom authenticators, and robust entity-level encryption/masking.
- **Static Analysis**: [PHPStan](https://phpstan.org/) for type safety and [Doctrine Doctor](https://github.com/ahmed-bhs/doctrine-doctor) for schema integrity.
- **UI/UX**: Twig, Stimulus, and Turbo for a reactive, "glassmorphic" interface.

---

## 🏗️ Architectural Refactoring (Doctrine Doctor)

The project recently underwent a major architectural cleanup to align with best-in-class Doctrine patterns:

1.  **Value Objects (Embeddables)**:
    - `User::email` refactored to `EmailAddress` value object.
    - `User::firstName` & `User::lastName` refactored to `PersonName` value object.
2.  **Schema Integrity**:
    - Renamed reserved SQL tables: `User` → `users`, `Group` → `study_groups`.
    - Switched `PasswordResetToken` ID from integer to secure **UUIDs**.
3.  **Performance & Config**:
    - Explicit server versioning in `doctrine.yaml`.
    - Optimized system and result cache pools in `framework.yaml`.
    - Strict property-path synchronization in security providers and repositories.

---

## 🚥 Getting Started

### Prerequisites

- PHP 8.2 or higher
- Composer
- Symfony CLI
- MySQL 8.0
- Python 3.9+ (for microservices)

### Installation

1.  **Clone the repository**:
    ```bash
    git clone [repository-url]
    cd studly
    ```

2.  **Install dependencies**:
    ```bash
    composer install
    pip install -r requirements.txt
    ```

3.  **Configure Environment**:
    Copy `.env` to `.env.local` and adjust your `DATABASE_URL` and Google API credentials.

4.  **Database Setup**:
    ```bash
    php bin/console doctrine:database:create
    php bin/console doctrine:schema:update --force
    ```

5.  **Run the Server**:
    ```bash
    symfony serve
    ```

---

## 🧪 Testing and Quality

Run static analysis and tests to ensure code quality:

```bash
# Static Analysis
vendor/bin/phpstan analyse

# Doctrine Integrity
php bin/console doctrine:schema:validate

# Unit Tests
php bin/phpunit
```

---

## 📄 License

This project is proprietary and confidential.

---
*Designed and built with ❤️ by the Studly Team.*
