<?php
    /* The navigation
     * Requires page that requires login.
     * How to highlight:
     * - JS: $('.homeNav').addClass('active');
     * Available:
     */
?>
<nav class="navbar navbar-inverse">
  <div class="container-fluid">
    <div class="navbar-header">
      <a class="navbar-brand homeNav" href="<?php echo buildUrl(''); ?>">Mini Brother</a>
    </div>
    <ul class="nav navbar-nav">
      <li class="homeNav"><a href="<?php echo buildUrl(''); ?>">Home</a></li>
          <li class="adminNav dropdown"><a class="dropdown-toggle" data-toggle="dropdown" href="#"></span>Dropdown<span class="caret"></span></a>
            <ul class="dropdown-menu">
              <li><a href="<?php echo buildUrl(''); ?>">Test</a></li>
            </ul>
          </li>
    </ul>
    <ul class="nav navbar-nav navbar-right">
	    <li class="userNav"><a href="<?php echo buildUrl(''); ?>">
            <?php echo getUser()->getFullName(); ?></a>
        </li>
        <li><a href="<?php echo buildUrl('logout.php'); ?>"><span class="glyphicon glyphicon-log-out"></span>Logout</a></li>
    </ul>
  </div>
</nav>
