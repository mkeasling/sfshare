<?php
/**
 * Created by IntelliJ IDEA.
 * User: mkeasling
 * Date: 11/27/15
 * Time: 6:24 PM
 */
use Sfshare\View;

?>
<div class="error alert alert-danger">
    <h4>Exception</h4>
    <p>
        <strong>Message: </strong>
        <?php echo View::instance()->exception->getMessage(); ?>
    </p>
</div>
