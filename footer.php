<footer class="site-footer">
    <div class="footer-grid">
        <div>
            <div class="footer-logo">MySystem</div>
            <p class="footer-copy">A clean personal dashboard experience, designed for easy registration and login flows.</p>
        </div>
        <div class="footer-links">
            <a href="index.php">Home</a>
            <a href="register.php">Register</a>
            <a href="login.php">Login</a>
        </div>
    </div>
    <div class="footer-bottom">
        <p>Made for simple PHP projects and fast access control.</p>
        <p>&copy; 2026 MySystem</p>
    </div>
</footer>

<style>
.site-footer {
    background: linear-gradient(180deg, rgba(15, 23, 42, 0.98), rgba(15, 23, 42, 0.92));
    color: #e5e7eb;
    padding: 32px 24px;
    border-radius: 28px;
    box-shadow: 0 22px 55px rgba(15, 23, 42, 0.14);
    margin-top: 38px;
}
.footer-grid {
    display: flex;
    flex-wrap: wrap;
    justify-content: space-between;
    gap: 24px;
    align-items: flex-start;
    max-width: 1180px;
    margin: 0 auto;
}
.footer-logo {
    font-size: 1.4rem;
    font-weight: 800;
    color: #60a5fa;
    margin-bottom: 10px;
}
.footer-copy {
    margin: 0;
    max-width: 420px;
    line-height: 1.7;
    color: #cbd5e1;
}
.footer-links {
    display: flex;
    gap: 18px;
    flex-wrap: wrap;
    align-items: center;
}
.footer-links a {
    color: #cbd5e1;
    text-decoration: none;
    font-weight: 600;
    transition: color 0.2s ease, transform 0.2s ease;
}
.footer-links a:hover {
    color: #93c5fd;
    transform: translateY(-1px);
}
.footer-bottom {
    display: flex;
    flex-wrap: wrap;
    justify-content: space-between;
    gap: 14px;
    align-items: center;
    margin-top: 28px;
    font-size: 0.95rem;
    color: #94a3b8;
}
@media (max-width: 760px) {
    .footer-grid {
        flex-direction: column;
    }
    .footer-bottom {
        flex-direction: column;
        align-items: flex-start;
    }
}
</style>
