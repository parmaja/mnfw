<?php
/**
* Render create the object and pass attributes to it
* $value is an attrubute of this object but you can pass render function instead of it
*/

function render($class, $value = null, $func = null) {
  if (is_array($value))
    $attributes = $value;
  else
    $attributes = null;

  $object = new $class($attributes);

  if (is_callable($value)) {
    $object->call($value);
  }

  if (is_callable($func))
    $object->render_func = $func;
  $object->render();
}

function print_quote($v, $q='"') {
  print $q.$v.$q;
}

/**
* Test the value then print name="value" with quote
* Useful for generate HTML
*/

function print_value($name, $value, $extra = '', $q='"')
{
  if (isset($value) && !empty($value))
  {
    if (!empty($name))
      print($name.'=');
    print_quote($value.$extra);
  }
}

/** Same above but add space before printing
*/
function _print_value($name, $value, $q='"') {
  if (isset($value) && !empty($value))
    print " ";
  print_value($name, $value, '', $q);
}

/**
*   Base Classes
*/

  class View {

    protected $app = null;

    public $attributes = array();
    public $render_func = null;

    function __construct($attributes) {

      if (isset($attributes))
        $this->attributes = $attributes;

/*    //another way, we will not use it.
      foreach($attributes as $attribute => $value) {
        $this->$attribute = $value;
      }*/

      if (method_exists($this, 'init')) //only for user define classes in his project
      {
        $new = $this->init();
        if (isset($new)) {
          $this->attributes = array_merge($this->attributes, $new);
        }
      }
    }

    function __destruct() {
    }


    function __call($method, $args) {

      if(is_callable($this->methods[$method]))
      {
        return call_user_func_array($this->methods[$method], $args);
      }
    }
/*
*  http://php.net/manual/en/language.oop5.overloading.php
*/

    public function __set($name, $value) {
      $this->attributes[$name] = $value;
    }

    public function &__get($name) {
      return $this->attributes[$name];
    }

    public function __isset($name)
    {
      return array_key_exists($name, $this->attributes);
    }

    public function __unset($name)
    {
      unset($this->attributes[$name]);
    }

    function __toString() {
      //TODO
    }

    public function call($func) {
    /* not now
      $f = \Closure::bind($func, $this, get_class());
      $f();
      */
      $func();
    }

    public function open() {
      if (method_exists($this, 'do_open'))
        $this->do_open();
    }

    public function close() {
      if (method_exists($this, 'do_close'))
        $this->do_close();
    }

    public function render() {
      $this->open();
      if (method_exists($this, 'do_render'))
        $this->do_render();
      $render_func = $this->render_func;
      if (is_callable($render_func))
        $render_func();
      $this->close();
    }

    public function process() {
      $this->do_process();
    }
  }

/**
*  Form Class
*/

  class FormView extends View {

    public function do_open() {
      if (isset($this->label)) {
      ?>
      <label <?php print_value('for', $this->name); ?> > <?php print $this->label; ?></label>
      <?php }  ?>
      <form <?php print_value('method', $this->method); _print_value('name', $this->name); _print_value('id', $this->id); _print_value('action', $this->action); ?>>
      <?php
    }

    public function do_close() {
      if (isset($this->submit)) {
      ?>
      <input type="submit" <?php print_value('value', $this->submit); ?> />
      <?php } ?>
      </form>
    <?php
    }
  }

/**
*  UI classes
*/

  class SelectView extends View {

    public function do_render() {

      if (isset($this->label)) {
      ?>
      <label for=<?php print_quote($this->name); ?> > <?php print $this->label; ?></label>
      <?php }
      ?>
      <select id=<?php print_quote($this->name) ?> name=<?php print_quote($this->name) ?>>
      <?php
        if ($this->add_empty) {
          print "<option value=''></option>";
        }
        if (isset($this->values)) {
          foreach($this->values as $id => $value)
            print "<option value='".$id."'>".$value."</option>";
      ?>
      </select>
      <?php
        }
    }
  }

  class InputView extends View {

    public function do_render() {
      if (!empty($this->label)) {
      ?>
      <label <?php print_value('for', $this->id); ?>> <?php print $this->label; ?></label>
      <?php }
        if (isset($this->type))
          $type = $this->type;
        else
          $type = 'text';
      ?>
      <input <?php print_value('type', $type); _print_value('class', $this->class); _print_value('id', $this->id); _print_value('name', $this->name); _print_value('value', $this->value); ?> />
      <?php
    }
  }

/**
*  Functions
*/

function OpenDiv($class = '', $id='') {
  print('<div'); _print_value('class', $class); _print_value('id', $id); print('>');
}

function CloseDiv() {
  print('</div>');
}

/**
*  $values is array, if you get it from PDO use PDO::FETCH_KEY_PAIR
*/

function print_select($name, $values, $attribs) {
  $label = $attribs['label'];
  $class = $attribs['class'];
  $selected = $attribs['selected'];
  $empty = $attribs['empty'];
  if (isset($label)) {
  ?>
  <label for=<?php print_quote($name); ?> > <?php print $label; ?></label>
  <?php } ?>
  <select id=<?php print_quote($name) ?> name=<?php print_quote($name) ?>>
  <?php
    if ($empty) {
      print "<option value=''></option>";
    }
    if (isset($values)) {
    foreach($values as $id => $value)
      print "<option value='".$id."'>".$value."</option>";
  ?>
  </select>
<?php
  }
}

?>