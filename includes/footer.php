<?php
// ============================================================
// ALMS — Common Dashboard Footer Include
// ============================================================
?>
    </div> <!-- /dashboard-shell -->

    <!-- Third Party Core Scripts (GSAP & ScrollTrigger) -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/ScrollTrigger.min.js"></script>
    
    <!-- System Core Main Scripts -->
    <script src="/assets/js/main.js"></script>
    
    <?php if (isset($extraJs)): ?>
        <?= $extraJs ?>
    <?php endif; ?>
</body>
</html>
