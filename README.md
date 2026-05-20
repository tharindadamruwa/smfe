# SMFE — Social Media For Education
## Math Question Community Platform

### File Structure
```
smfe/
├── index.php             ← Home feed (20 popular posts)
├── ask.php               ← Post a question (math keyboard, image upload)
├── login.php             ← Login page
├── signup.php            ← Sign up page
├── profile.php           ← User profile + liked posts
├── explore.php           ← Browse & search all questions
├── logout.php            ← Session destroy + redirect
│
├── style/
│   └── main.css          ← Full shared stylesheet (green/teal palette)
│
├── js/
│   ├── mathkb.js         ← Universal math keyboard (included on every page)
│   └── main.js           ← Navbar toggle, like, comments, share, toast
│
├── components/
│   ├── navbar.php        ← renderNavbar(), renderAskBar(), renderHead(), renderShareModal()
│   ├── post_card.php     ← renderPost($post) — single post card HTML
│   └── posts_data.php    ← getDummyPosts() — 20 dummy math questions
│
└── img/
    ├── system/
    │   ├── logo.svg
    │   └── default-avatar.svg
    └── uploads/           ← User-uploaded images go here (create & chmod 775)
```

### Setup (XAMPP / LAMP / WAMP)
1. Copy the `smfe/` folder to your web root (e.g. `htdocs/smfe`)
2. Create the uploads directory:
   ```bash
   mkdir -p smfe/img/uploads
   chmod 775 smfe/img/uploads
   ```
3. Visit `smfe.rf.gd` in your browser

### Next Steps (Backend Integration)
- Connect a MySQL database for users, posts, comments, likes
- Replace `getDummyPosts()` with real DB queries
- Implement session-based auth (the session scaffold is already in place)
- Move image upload handling to server-side PHP in `ask.php`
- Add popularity score (likes × 3 + comments) for feed ranking

### Math Keyboard
- Included on every page via `<script src="js/mathkb.js"></script>`
- Attach to any input by adding `class="mathkb-trigger"` + `data-target="#inputId"` to a button
- Sections: English alphabet, Greek alphabet, Math functions (sin, cos, Γ, …), Symbols with editable templates (definite integral a/b, sigma bounds, limits, etc.)

### Color Palette
| Variable          | Hex       | Use                  |
|-------------------|-----------|----------------------|
| `--primary`       | `#10b981` | Emerald green        |
| `--teal`          | `#0d9488` | Teal / green-blue    |
| `--accent`        | `#06b6d4` | Cyan accent          |
| `--bg`            | `#f0fdf9` | Page background      |
| `--surface`       | `#ecfdf5` | Cards, chips         |
