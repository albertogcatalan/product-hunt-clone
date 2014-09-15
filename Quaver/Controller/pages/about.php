<?php
/*
 * Copyright (c) 2014 Alberto González
 * Distributed under Apache License 2.0
 * (see README for details)
 */

namespace Quaver\Controller;

$template = $this->twig->loadTemplate('pages/about.twig');
echo $template->render($this->twigVars);

?>