<?php

class opTwipnePluginConfiguration extends sfPluginConfiguration
{
  public function initialize()
  {
    sfToolkit::addIncludePath(array(
      OPENPNE3_CONFIG_DIR.'/../lib/vendor/',
    ));
  }
}
