<?php
// Legacy endpoint disabled: use cart.php -> checkout.php instead.
// This prevents old code from submitting hard-coded placeholder shipping data to CJ.
header('Location: cart.php', true, 303);
exit;
