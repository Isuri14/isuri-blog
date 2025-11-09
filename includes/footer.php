<!-- ======= FOOTER ======= -->
<footer class="footer">
  <div class="footer-container">
    <p>&copy; <?= date('Y'); ?> <strong>BlogWithMe</strong>. All rights reserved.</p>
    <p>Developed by <strong>K.H.I. Hansani</strong></p>
  </div>
</footer>

<style>
.footer {
    background-color: #111827;
    color: #f3f4f6;
    text-align: center;
    padding: 20px 15px;
    margin-top: 40px;
    font-family: 'Inter', sans-serif;
}
.footer-container {
    max-width: 1200px;
    margin: 0 auto;
}
.footer p {
    margin: 6px 0;
    font-size: 0.9em;
    line-height: 1.5;
}
.footer strong {
    color: #3b82f6;
}
@media (max-width: 768px) {
    .footer {
        padding: 18px 10px;
        font-size: 0.85em;
    }
}
</style>
