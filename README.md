<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Chaos CRUD Readme</title>
    <style>
      :root {
        font-size: 1.1rem;
      }
    </style>
  </head>
  <body>
    <h1>Chaos CRUD Readme</h1>
    <h2>Create - Read - Update - Delete</h2>
    <h2>Instantiation</h2>
    <p>
      This may vary a little depending on how you autoload your classes (if at
      all). Asuming you use Composer and have the proper namespaces autoloaded,
      you can instantiate by doing the following:
    </p>
    <code> $db = new Chaos\Network\CRUD(); </code>
    <p>
      Alternatively, if you are using my classDeclarations.php file, you do not
      need to instantiate it; however, it will only run one instance of the
      class when using <code>db()-></code>
    </p>
    <p>
      It should be noted that connection info can be grabbed 1 of 3 ways:
      <ul>
        <li>During Instantiation: db('localhost','db_name','db_username','db_password')</li>
        <li>Using ENV: $_ENV['DB_HOST'], $_ENV['DB_NAME'], $_ENV['DB_USER'], $_ENV['DB_PASS']</li>
        <li>Using SESSIONs: $_SESSION['DB_HOST'] (same as ENV keys)</li>
      </ul>
    </p>
    <h3>Note</h3>
    <p>
      For this next section, I'm going to assume you are using my
      classDeclarations.php method. If you are not, then just replace db() with
      whatever your instantiated class is
    </p>
    <code>
      db()->table()<br />
      vs<br />
      $db = new Chaos\Network\CRUD();<br />
      $db->table();
    </code>
    <h2>Method Chaining</h2>
    <p>
      The Class is built with method chaining in mind in order to make a
      smoother process.
    </p>
    <h3>Example:</h3>
    <code>
      db()->table('users')<br />
      ->create(['username'=>'jdoe',<br />
      &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'password'=>'test'])<br />
      ->query();
    </code>
    <h3>Alterative Example <em>without</em> Method Chaining</h3>
    <code>
      db()->table('users');<br />
      db()->create(['username'=>'jdoe',<br />
      &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'password'=>'test']);<br />
      db()->query();
    </code>
    <p>Both function the same.</p>
    <h2>Methods</h2>
    <p>Let's break these down based on the usage in each part of the CRUD</p>
    <h3>All Methods</h3>
    <ul>
      <li>
        table('users') // This line is required prior to ->query() being invoked
        <strong> <em> EVERY TIME </em> </strong>.
      </li>
      <li>
        query(); // This is the trigger for all the methods. It
        <strong><em>MUST</em></strong> be the last piece of the chain, or after
        all non-chained methods are completed.
      </li>
    </ul>
    <h3>Create</h3>
    <ul>
      <li>
        getLastID(bool) // This let's the query know if you want returned the
        last row of inserted data's primary key id. Be sure that if you are
        chaining, you start with a variable assignment for the lastID to be
        placed into.
        <ul>
          <li>
            $lastID =
            db()->table('users')-><strong>getLastID(true)</strong>->insert(['username','bob'])->query();
          </li>
        </ul>
      </li>
      <li>
        create(['column' => 'value']) // You can list as many as you need, but
        they should always be in an associatiave array format of which column
        and the value to be assigned to that column.
      </li>
    </ul>
    <p>Create is one of the simplier ones. There's no WHERE, LIMIT, etc.</p>
    <h3>Read</h3>
    <p>Used to SELECT data from the table</p>
    <ul>
      <li>
        addColumn() // This is where you can add the columns you want to filter
        by. You can use a string or array of strings (not an associated array)
        such as: (['username','password']). If you do not want to filter it,
        leave it empty. The system will default it to *
      </li>
      <li>
        addCondition($column, $value, $operand) // This needs 3 pieces for the
        WHERE statement, the column you're filtering by, the value(s) you want
        to include, and the operand you'll be using. ('username', 'jdoe', '=')
        || ('username', 'jd%', 'LIKE') - Notice the % on the one with 'LIKE' -
        You need to include the wildcard symbol `%` when necessary. Also, '=' is
        the default, so if only 2 properties are sent, it will assume '='
      </li>
      <li>
        addOrder($string) // Accepts a single string argument, but you can use
        it multiple times if you want multiple ORDER BY.
      </li>
      <li>
        addDir('DESC') // it assumes 'ASC', but if you prefer descending order,
        you can set it here.
      </li>
      <li>
        addLimit(2) // Limits the number of returned results to the integer
        provided. Must be greater than 0 and an integer (not float, string, or
        other type).
      </li>
      <li>
        read() // Notice the change from create to read - this is how the class
        knows which type of query you want to do.
      </li>
    </ul>
    <h3>Update</h3>
    <ul>
      <li>addCondition()</li>
      <li>
        setSafe(bool) // This is a protection method to help prevent you from
        accidently updating EVERY row. It defaults to true (limiting your amount
        of changes to 1 row unless limit() is declared)
      </li>
      <li>limit()</li>
      <li>
        update(['column'=>'new value']) // As with create, you can put as many
        rows of an associative array as you need in here. One for each column.
      </li>
      <li>update</li>
    </ul>
    <h3>Delete</h3>
    <ul>
      <li>addCondition()</li>
      <li>setSafe()</li>
      <li>addLimit()</li>
      <li>delete()</li>
    </ul>
    <h3>Empty</h3>
    <p>
      The only thing you need for emptying a table and resetting the auto-increment to 1 is the table name:<br>
      // db()->table('tableName')->empty()->query();
    </p>
    <h3>Notes</h3>
    <ul>
      <li>
        setSafe(bool) - If you have an environmental value for
        <strong>$_ENV['SAFETY_LIMIT']</strong>, and no limit set with
        addLimit(), it will default the limit to your SAFETY_LIMIT value.
        Otherwise, it defaults to 1.
      </li>
      <ul>
        <h4>Order of Limits for Update and Delete (when setSafe(true) or not set)</h4>
        <li>addLimit(int)</li>
        <li>$_ENV['SAFETY_LIMIT']</li>
        <li>1</li>
      </ul>
    </ul>
  </body>
</html>