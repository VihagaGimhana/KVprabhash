# L'Étoile — Modern Parisian Gastronomy & Lounge

A premium, fully responsive 5-page culinary restaurant website engineered using **HTML5, CSS3, JavaScript (ES6+), jQuery, AJAX, and PHP**. 

This project was built to meet and exceed all academic criteria for university-level web design assessments, utilizing plain **HTML5** pages for the interactive frontend and secure **PHP** endpoints to drive asynchronous AJAX database writes.

---

## 🌟 Implemented Features & Interactivity

1.  **Immersive Landing Page (`index.html`)**: Features fluid parallax-like hero headers using high-res stock photography, dynamic signature menus, and call-to-actions.
2.  **AJAX Live Category & Dietary Filter Menu (`menu.html`)**:
    *   Dynamic course filters (All, Starters, Plats, Desserts, Cocktails).
    *   Interactive dietary checkbox controls (Vegan, Gluten-Free, Organic, Chef's Specials) that use jQuery DOM filtering with smooth fade transitions.
3.  **"Gold Class" Wine Pairing Sommelier (`menu.html` widget)**:
    *   An interactive drop-down assistant where selecting a signature main course triggers jQuery animations and suggests matching French vintage wine pairings.
4.  **AJAX Seating Chart Reservation Engine (`reservations.html`)**:
    *   **Interactive SVG Floor Plan**: Clickable vector tables of various seating sizes.
    *   **Live AJAX Seating Queries (GET)**: Changing the booking date or time slot triggers an AJAX query to the PHP API to load and display occupied tables instantly (graying them out in real-time).
    *   **Asynchronous Booking Submissions (POST)**: Validates contact details and guest counts on the spot and writes to a database via AJAX without page refreshes.
5.  **Interactive FAQ Accordion & Contact Inquiries (`contact.html`)**:
    *   AJAX form sending customer inquiries asynchronously to the PHP API.
    *   A smooth, custom jQuery slide-toggling FAQ Accordion.
6.  **Interactive SVG Parisian Map (`contact.html`)**:
    *   A custom vector SVG map of Paris's 1st Arrondissement detailing L'Étoile's location, featuring pulsing golden pins and custom interactive landmark hover tooltips.

---

## 📂 Project Architecture

```text
/project.w
│   index.html              # Home Landing Page (Hero, Philosophy, Signatures)
│   menu.html               # Interactive Category & Dietary Filter Menu Page
│   story.html              # Culinary Heritage & Milestone Timeline Page
│   reservations.html       # Live AJAX SVG Booking Engine Page
│   contact.html            # Contact Form, Accordion FAQ & Paris SVG Map Page
│   README.md               # Deployment and configuration guide
│
├───api                     # RESTful PHP API Endpoints (AJAX Backends)
│       reserve.php         # Processes seating availability checks & new bookings
│       contact.php         # Processes contact inquiry letters
│
├───data                    # Persistent JSON Datastores
│       reservations.json   # Stores bookings persistently
│       messages.json       # Stores contact messages persistently
│       menu.json           # Master culinary menu dataset
│
├───css                     # Modular CSS Stylesheets
│       index.css           # Global design system (Google Fonts, tokens, fluid scales)
│
└───js                      # Modular Javascript & jQuery
        app.js              # Core interactive scripts, maps, AJAX submissions
```

---

## 🚀 How to Run the Website Locally

Since the frontend consists of plain `.html` files, you can double-click and open the pages directly in your browser. However, **to run the interactive APIs (the live booking seat-reservation checking and email contact storage)**, the files must be hosted on a local server supporting **PHP**.

### Option A: Using XAMPP (Recommended for University Demos)
1.  Download and install [XAMPP](https://www.apachefriends.org/).
2.  Open the **XAMPP Control Panel** and click **Start** next to **Apache**.
3.  Navigate to your XAMPP installation directory (usually `C:\xampp\htdocs` on Windows).
4.  Copy the entire contents of this project folder (`project.w`) into a new folder named `letoile` inside `htdocs` (e.g., `C:\xampp\htdocs\letoile`).
5.  Open your browser and go to: `http://localhost/letoile/index.html`.

### Option B: Using VS Code (Lightning Fast)
1.  Open this project folder in **Visual Studio Code**.
2.  Install the extension called **"PHP Server"** by *brapansin*.
3.  Right-click on `index.html` in your sidebar.
4.  Select **"PHP Server: Serve Project"**.
5.  A browser tab will automatically launch serving the project at `http://localhost:3000`.

### Option C: Using Command-Line PHP (If installed globally)
1.  Open terminal in this directory (`project.w`).
2.  Run the command:
    ```bash
    php -S localhost:8000
    ```
3.  Open browser and navigate to: `http://localhost:8000`.

---

## 🔒 Security & Data Management

*   **Sanitization**: The PHP endpoints filter and sanitize all inputs against cross-site scripting (XSS) and injection attempts using `filter_input` and `htmlspecialchars`.
*   **Double-Booking Protection**: The reservation engine validates selections server-side to guarantee that no two users can book the same table for the same date and time slot simultaneously.
*   **Server-Side Validation**: Complements Javascript client checks, ensuring data integrity even if client scripts are bypassed.
