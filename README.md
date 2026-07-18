# CodeLensAI

> AI-powered code review for continuous learning.

Analyze GitHub repositories with AI, receive actionable feedback,
and continuously improve your code quality through an intuitive review experience.

<p align="center">
  <img src="docs/images/hero.png" alt="CodeLensAI Hero" width="100%">
</p>

---

## 🎬 Demo

![AI Review](docs/images/review.gif)

> Watch CodeLensAI analyze a GitHub repository, detect issues, and generate AI-powered improvements in real time.

---

## 📸 Key Screens

| Dashboard | AI Review |
|-----------|-----------|
| ![Dashboard](docs/images/dashboard.jpg) | ![AI Review](docs/images/review.jpg) |

| Ranking | Library |
|---------|---------|
| ![Ranking](docs/images/ranking.jpg) | ![Library](docs/images/library.jpg) |

---

## ✨ Features

- **AI Code Review** — Instant analysis powered by Claude (Anthropic)
- **Learning Mode** — Reviews written to teach, not just critique
- **Rankings** — Gamified progress tracking across your repositories
- **Library** — Save and revisit your best learnings
- **🤖 Meet CodeLens-kun** — Your AI learning companion.
- **Notion Integration** — Sync insights directly to your workspace

---

## 🚀 Getting Started

```bash
git clone https://github.com/mize1978/codelens-ai.git
cd codelens-ai

composer install
cp .env.example .env
php artisan key:generate

npm install
npm run dev

php artisan migrate
php artisan serve
```

Open [http://localhost:8000](http://localhost:8000)

---

## 🛠 Tech Stack

| Layer    | Tech                        |
|----------|-----------------------------|
| Backend  | Laravel 13                  |
| Frontend | Blade + Vite + Bootstrap    |
| AI       | Claude API (Anthropic)      |
| Database | MySQL                       |
| Language | PHP 8.3                     |
| Deploy   | Render                      |

---

## 🛣️ Roadmap

| Phase  | Items                                              |
|--------|----------------------------------------------------|
| Next   | Review UI / AI Feedback Improvement / GitHub OAuth |
| Soon   | Team Review / Review History / Markdown Export     |
| Future | VS Code Extension / CLI / SaaS Platform            |

---

## 📄 License

MIT
