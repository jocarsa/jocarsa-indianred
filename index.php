<?php
// index.php
error_reporting(E_ALL);
ini_set('display_errors', 1);
/*******************************************************
 * Load configuration
 *******************************************************/
session_start();

// For demonstration, we hard-code user/pass and db folder.
// In your real setup, you'd require 'config.php' or similar.
$username   = 'administrador';
$password   = 'Trag4luz$';
$dbFolder   = '../databases/'; // path to your .sqlite files

/*******************************************************
 * Handle Login & Logout
 *******************************************************/
// Logout
if (isset($_GET['logout']) && $_GET['logout'] === 'true') {
    session_destroy();
    echo '<meta http-equiv="refresh" content="1; URL=?" />';
    exit;
}

// If a login form was submitted...
if (isset($_POST['login']) && $_POST['login'] === 'true') {
    if ($_POST['username'] === $username && $_POST['password'] === $password) {
        $_SESSION['logged_in'] = true;
        echo '<meta http-equiv="refresh" content="1; URL=?" />';
        exit;
    } else {
        $loginError = "Credenciales inválidas. Por favor, inténtalo de nuevo.";
    }
}

// If not logged in, show login form only, then exit
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>jocarsa | indianred</title>
        <link rel='icon' type='image/svg+xml' href='https://jocarsa.com/static/logo/indianred.png' />
        <link rel="stylesheet" href="estilo.css">
    </head>
    <body>
        <div id="wpadminbar">
            <a href="#" class="logo">
                <img src="https://jocarsa.com/static/logo/indianred.png" alt="Logo"> 
                jocarsa | indianred
            </a>
        </div>
        
        <div class="login-wrapper">
            <div class="login-container">
                <h2>Panel de Control</h2>
                <?php if (isset($loginError)) { ?>
                    <div class="error"><?php echo $loginError; ?></div>
                <?php } ?>
                <form method="post" class="login-form">
                    <input type="hidden" name="login" value="true">

                    <label for="username">Usuario:</label>
                    <input type="text" id="username" name="username" required>

                    <label for="password">Contraseña:</label>
                    <input type="password" id="password" name="password" required>

                    <input type="submit" value="Acceder">
                </form>
            </div>
        </div>
    </body>
    </html>
    <?php
    exit;
}

/*******************************************************
 * Determine Selected Database
 *******************************************************/
$selectedDb = isset($_GET['db']) ? basename($_GET['db']) : null; // Use basename to prevent directory traversal

/*******************************************************
 * Establish PDO Connection
 *******************************************************/
try {
    if ($selectedDb) {
        $dbPath = realpath($dbFolder . $selectedDb);
        // Verify that the database exists and is within the target folder
        if ($dbPath && strpos($dbPath, realpath($dbFolder)) === 0 && file_exists($dbPath)) {
            $pdo = new PDO("sqlite:" . $dbPath);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } else {
            throw new Exception("Base de datos no encontrada.");
        }
    } else {
        $pdo = null; // No database selected yet
    }
} catch (Exception $e) {
    die("Error de conexión: " . $e->getMessage());
}

/*******************************************************
 * If No Database Selected, Show Database Selection Grid
 *******************************************************/
if (!$selectedDb) {
    // Scan the target folder for SQLite databases
    $databases = array_filter(scandir($dbFolder), function($file) use ($dbFolder) {
        $filePath = $dbFolder . $file;
        return is_file($filePath) && preg_match('/\.(sqlite|db|sqlite3)$/i', $file);
    });
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>Selecciona una Base de Datos - jocarsa | indianred</title>
        <link rel='icon' type='image/svg+xml' href='https://jocarsa.com/static/logo/indianred.png' />
        <link rel="stylesheet" href="estilo.css">
        <style>
            /* Additional styles for the grid */
            .db-grid {
                display: grid;
                grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
                gap: 20px;
                margin-top: 20px;
            }
            .db-card {
                background: #fafafa;
                border: 1px solid #ddd;
                border-radius: 5px;
                padding: 20px;
                text-align: center;
                box-shadow: 0 2px 5px rgba(0,0,0,0.1);
                transition: transform 0.2s, box-shadow 0.2s;
            }
            .db-card:hover {
                transform: translateY(-5px);
                box-shadow: 0 4px 10px rgba(0,0,0,0.2);
            }
            .db-card a {
                text-decoration: none;
                color: #333;
                font-size: 18px;
                font-weight: bold;
            }
        </style>
    </head>
    <body>
    <div id="wpadminbar">
        <a href="#" class="logo">
            <img src="https://jocarsa.com/static/logo/indianred.png" alt="Logo"> 
            jocarsa | indianred
        </a>
        <a href="?logout=true" class="logout-link">Cerrar sesión</a>
    </div>

    <div id="wpwrap">
        <div id="adminmenu">
            <h2>Base de datos</h2>
            <ul>
                <!-- New SQL link is hidden in database selection view -->
            </ul>
        </div>

        <div class="wrap">
            <h1>Selecciona una Base de Datos</h1>
            <?php if (empty($databases)): ?>
                <p>No se encontraron bases de datos en la carpeta especificada.</p>
            <?php else: ?>
                <div class="db-grid">
                    <?php foreach ($databases as $db): 
                        $file = 'https://jocarsa.com/static/logo/'.explode(".",$db)[0].".png";
                        $file_headers = @get_headers($file);
                        if(!$file_headers || $file_headers[0] == 'HTTP/1.1 404 Not Found') {
                            $pon = "db.svg";
                        }
                        else {
                            $pon = $file;
                        }
                    ?>
                    
                    <a href="?db=<?php echo urlencode($db); ?>">
                        <div class="db-card">
                            <img src="<?php echo $pon ;?>">
                            <p><?php echo htmlspecialchars($db); ?></p>
                        </div>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- (Optional) Additional scripts or styles -->
    <link rel="stylesheet" href="https://jocarsa.github.io/jocarsa-snow/jocarsa%20%7C%20snow.css">
    <script src="https://jocarsa.github.io/jocarsa-snow/jocarsa%20%7C%20snow.js" defer></script>
    
    <link rel="stylesheet" href="https://jocarsa.github.io/jocarsa-seashell/jocarsa%20%7C%20seashell.css">
    <script src="https://jocarsa.github.io/jocarsa-seashell/jocarsa%20%7C%20seashell.js" defer></script>
    </body>
    </html>
    <?php
    exit;
}

/*******************************************************
 * Helper functions
 *******************************************************/

/**
 * Returns an associative array of the form:
 *   [ 
 *     ["value" => "some_value", "count" => 5],
 *     ...
 *   ]
 * for each distinct value of the specified column.
 */
function getColumnValueCounts($pdo, $table, $column) {
    $stmt = $pdo->prepare("SELECT \"$column\" AS value, COUNT(*) AS count
                           FROM \"$table\"
                           GROUP BY \"$column\"");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getTables($pdo) {
    $stmt = $pdo->query("SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%' ORDER BY name");
    return $stmt->fetchAll(PDO::FETCH_COLUMN);
}

function getColumns($pdo, $table) {
    $stmt = $pdo->query("PRAGMA table_info(\"$table\")");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getRows($pdo, $table) {
    $stmt = $pdo->query("SELECT * FROM \"$table\"");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/*******************************************************
 * Handle CRUD Operations for Selected Database
 *******************************************************/
$table = isset($_GET['table']) ? $_GET['table'] : null;

// Insert
if (isset($_POST['action']) && $_POST['action'] === 'insert' && $table) {
    $columns = getColumns($pdo, $table);
    $colNames = [];
    $placeholders = [];
    $values = [];

    foreach ($columns as $col) {
        $colName = $col['name'];
        // Skip PK if autoincrement and not provided
        if (!isset($_POST[$colName]) || ($_POST[$colName] === '' && $col['pk'] == 1)) {
            continue;
        }
        $colNames[] = $colName;
        $placeholders[] = ':' . $colName;
        if (stripos($col['type'], 'BOOL') !== false || stripos($col['type'], 'BOOLEAN') !== false) {
            $values[':' . $colName] = isset($_POST[$colName]) ? 1 : 0;
        } else {
            $values[':' . $colName] = $_POST[$colName];
        }
    }

    if (!empty($colNames)) {
        $sql = "INSERT INTO \"$table\" (" . implode(',', $colNames) . ") VALUES (" . implode(',', $placeholders) . ")";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($values);
    }
}

// Update
if (isset($_POST['action']) && $_POST['action'] === 'update' && $table) {
    $id = $_POST['id'];
    $columns = getColumns($pdo, $table);
    $sets = [];
    $values = [':id' => $id];

    // Identify primary key
    $primaryKey = null;
    foreach ($columns as $col) {
        if ($col['pk'] == 1) {
            $primaryKey = $col['name'];
            break;
        }
    }

    // Build sets
    foreach ($columns as $col) {
        $colName = $col['name'];
        // If posted or boolean
        if (isset($_POST[$colName]) ||
            (stripos($col['type'], 'BOOL') !== false || stripos($col['type'], 'BOOLEAN') !== false)) {
            $sets[] = "\"$colName\" = :$colName";
            if (stripos($col['type'], 'BOOL') !== false || stripos($col['type'], 'BOOLEAN') !== false) {
                $values[':' . $colName] = isset($_POST[$colName]) ? 1 : 0;
            } else {
                $values[':' . $colName] = $_POST[$colName];
            }
        }
    }

    if (!empty($sets) && $primaryKey !== null) {
        $sql = "UPDATE \"$table\" SET " . implode(',', $sets) . " WHERE \"$primaryKey\" = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($values);
    }
}

// Delete
if (isset($_GET['action']) && $_GET['action'] === 'delete' && $table) {
    $id = $_GET['id'];
    $columns = getColumns($pdo, $table);
    $primaryKey = null;
    foreach ($columns as $col) {
        if ($col['pk'] == 1) {
            $primaryKey = $col['name'];
            break;
        }
    }
    if ($primaryKey !== null) {
        $sql = "DELETE FROM \"$table\" WHERE \"$primaryKey\" = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':id' => $id]);
    }
}

/*******************************************************
 * Handle custom SQL (only SELECT)
 *******************************************************/
$sqlResults = [];
$sqlColumns = [];
$sqlError   = null;

// Check if we are in "SQL mode"
$action = isset($_GET['action']) ? $_GET['action'] : null;

if ($action === 'sql' && isset($_POST['sql_query'])) {
    // Retrieve user query
    $sqlQuery = trim($_POST['sql_query']);

    // Basic check to allow only SELECT statements
    if (strncasecmp($sqlQuery, 'select', 6) === 0) {
        try {
            $stmt = $pdo->query($sqlQuery);
            $sqlResults = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Dynamically get columns from the first row, if any
            if (count($sqlResults) > 0) {
                $sqlColumns = array_keys($sqlResults[0]);
            }
        } catch (Exception $e) {
            $sqlError = $e->getMessage();
        }
    } else {
        $sqlError = "Solo se permiten sentencias SELECT en esta consola.";
    }
}

/*******************************************************
 * Render the UI
 *******************************************************/
$tables = $selectedDb ? getTables($pdo) : [];

// Prepare data for charts in $chartsData. We'll build it after we know which table is selected.
$chartsData = [];

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>jocarsa | indianred</title>
<link rel='icon' type='image/svg+xml' href='https://jocarsa.com/static/logo/indianred.png' />
<link rel="stylesheet" href="estilo.css">
</head>
<body>
<div id="wpadminbar">
    <a href="?logout=true" class="logout-link">Cerrar sesión</a>
    <?php if ($selectedDb): ?>
        <a href="?db=<?php echo urlencode($selectedDb); ?>" class="logo">
            <img src="https://jocarsa.com/static/logo/indianred.png" alt="Logo"> 
            jocarsa | indianred
        </a>
    <?php else: ?>
        <a href="#" class="logo">
            <img src="https://jocarsa.com/static/logo/indianred.png" alt="Logo"> 
            jocarsa | indianred
        </a>
    <?php endif; ?>
</div>

<div id="wpwrap">
    <div id="adminmenu">
        <h2>Base de datos</h2>
        <ul>
            <?php if ($selectedDb): ?>
                <?php foreach ($tables as $t): ?>
                    <li>
                        <a href="?db=<?php echo urlencode($selectedDb); ?>&table=<?php echo urlencode($t); ?>"
                           class="<?php echo ($t == $table && $action !== 'sql') ? 'current' : ''; ?>">
                           <?php echo htmlspecialchars($t); ?>
                        </a>
                    </li>
                <?php endforeach; ?>
                
                <!-- SQL Link -->
                <li>
                    <a href="?db=<?php echo urlencode($selectedDb); ?>&action=sql" class="<?php echo ($action === 'sql') ? 'current' : ''; ?>">
                        SQL
                    </a>
                </li>

                <!-- Go Back to Database Selection -->
                <li>
                    <a href="?" class="logout-link">← Volver a Bases de Datos</a>
                </li>
            <?php endif; ?>
        </ul>
    </div>

    <div class="wrap">
        <?php if ($selectedDb): ?>
            <h1>Panel de Control - <?php echo htmlspecialchars($selectedDb); ?></h1>
        <?php else: ?>
            <h1>Selecciona una Base de Datos</h1>
        <?php endif; ?>

        <!-- If in SQL mode, show the SQL console pane -->
        <?php if ($action === 'sql' && $selectedDb): ?>
            <h2>Consola SQL (solo SELECT)</h2>
            <form method="post" class="form-section">
                <label for="sql_query">Introduce tu sentencia SELECT:</label><br>
                <textarea name="sql_query" id="sql_query" rows="4" style="width:100%;"></textarea><br><br>
                <input type="submit" value="Ejecutar">
            </form>

            <?php if ($sqlError): ?>
                <div class="error" style="margin-top:10px;">
                    <?php echo htmlspecialchars($sqlError); ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($sqlResults)): ?>
                <h3>Resultados de la consulta</h3>
                <div class="table-container table-section">
                    <table>
                        <tr>
                            <?php foreach ($sqlColumns as $colName): ?>
                                <th><?php echo htmlspecialchars($colName); ?></th>
                            <?php endforeach; ?>
                        </tr>
                        <?php foreach ($sqlResults as $row): ?>
                            <tr>
                                <?php foreach ($sqlColumns as $colName): ?>
                                    <td><?php echo htmlspecialchars($row[$colName]); ?></td>
                                <?php endforeach; ?>
                            </tr>
                        <?php endforeach; ?>
                    </table>
                </div>
            <?php endif; ?>
        
        <?php elseif ($selectedDb && $table): ?>
            <!-- Show table operations -->
            <?php
                $columns = getColumns($pdo, $table);
                $rows = getRows($pdo, $table);

                // Identify primary key
                $primaryKey = null;
                foreach ($columns as $col) {
                    if ($col['pk'] == 1) {
                        $primaryKey = $col['name'];
                        break;
                    }
                }

                /*******************************************************
                 * Build data for charts
                 *******************************************************/
                foreach ($columns as $col) {
                    $colName = $col['name'];
                    $valueCounts = getColumnValueCounts($pdo, $table, $colName);

                    // If there is more than 1 distinct value, let's consider it "repetitive" for a chart
                    if (count($valueCounts) > 1) {
                        $labels = [];
                        $data   = [];
                        foreach ($valueCounts as $vc) {
                            // Convert NULL to a readable label
                            $labelValue = ($vc['value'] !== null) ? $vc['value'] : '(NULL)';
                            $labels[] = $labelValue;
                            $data[]   = (int)$vc['count'];
                        }
                        // Store for our JS to consume
                        $chartsData[$colName] = [
                            'labels' => $labels,
                            'data'   => $data
                        ];
                    }
                }
            ?>

            <h2>Tabla: <?php echo htmlspecialchars($table); ?></h2>
				<div id="supercontenedor">
            <!-- Chart container -->
            <div id="charts-container" style="margin-bottom:30px;">
                <!-- JS code will generate Pie Charts here (one per column with repetitive data) -->
            </div>
            </div>

            <h3>Inserta un nuevo registro</h3>
            <form method="post" class="form-section">
                <input type="hidden" name="action" value="insert">
                <input type="hidden" name="table" value="<?php echo htmlspecialchars($table); ?>">
                <?php foreach ($columns as $col): ?>
                    <div>
                        <label><?php echo htmlspecialchars($col['name']); ?>:</label>
                        <?php if (stripos($col['type'], 'BOOL') !== false || stripos($col['type'], 'BOOLEAN') !== false): ?>
                            <input type="checkbox" name="<?php echo htmlspecialchars($col['name']); ?>" value="1">
                        <?php elseif ($col['name'] === 'content'): ?>
                            <textarea name="<?php echo htmlspecialchars($col['name']); ?>"></textarea>
                        <?php else: ?>
                            <input type="text" name="<?php echo htmlspecialchars($col['name']); ?>">
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
                <div>
                    <input type="submit" value="Insert">
                </div>
            </form>

            <h3>Registros previos</h3>
            <div class="table-container table-section">
                <table>
                    <tr>
                        <?php foreach ($columns as $col): ?>
                            <th><?php echo htmlspecialchars($col['name']); ?></th>
                        <?php endforeach; ?>
                        <th>Acciones</th>
                    </tr>
                    <?php foreach ($rows as $row): ?>
                        <tr>
                            <?php foreach ($columns as $col): ?>
                                <td>
                                    <?php 
                                        $cell = (string)$row[$col['name']];
                                        $cell = htmlspecialchars($cell);
                                        echo (strlen($cell) > 100) 
                                                ? substr($cell, 0, 100) . "..." 
                                                : $cell;
                                    ?>
                                </td>
                            <?php endforeach; ?>
                            <td class="action-links">
                                <a href="?db=<?php echo urlencode($selectedDb); ?>&table=<?php echo urlencode($table); ?>&edit=<?php echo urlencode($row[$primaryKey]); ?>">Edit</a>
                                <a href="?db=<?php echo urlencode($selectedDb); ?>&table=<?php echo urlencode($table); ?>&action=delete&id=<?php echo urlencode($row[$primaryKey]); ?>"
                                   onclick="return confirm('¿Estás seguro de que deseas eliminar este registro?');">Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </table>
            </div>

            <?php 
            // Edit form if needed
            if (isset($_GET['edit'])) {
                $editId = $_GET['edit'];
                $stmt = $pdo->prepare("SELECT * FROM \"$table\" WHERE \"$primaryKey\" = :id");
                $stmt->execute([':id' => $editId]);
                $editRow = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($editRow) {
            ?>
            <h3>Edita un registro (ID: <?php echo htmlspecialchars($editId); ?>)</h3>
            <form method="post" class="form-section edit-form">
                <input type="hidden" name="action" value="update">
                <input type="hidden" name="table" value="<?php echo htmlspecialchars($table); ?>">
                <input type="hidden" name="id" value="<?php echo htmlspecialchars($editId); ?>">
                <?php foreach ($columns as $col): ?>
                    <div>
                        <label><?php echo htmlspecialchars($col['name']); ?>:</label>
                        <?php if (stripos($col['type'], 'BOOL') !== false || stripos($col['type'], 'BOOLEAN') !== false): ?>
                            <input type="checkbox" name="<?php echo htmlspecialchars($col['name']); ?>" value="1"
                                   <?php echo ($editRow[$col['name']]) ? 'checked' : ''; ?>>
                        <?php elseif ($col['name'] === 'content'): ?>
                            <textarea name="<?php echo htmlspecialchars($col['name']); ?>">
                                <?php echo htmlspecialchars($editRow[$col['name']]); ?>
                            </textarea>
                        <?php else: ?>
                            <input type="text" name="<?php echo htmlspecialchars($col['name']); ?>" 
                                   value="<?php echo htmlspecialchars($editRow[$col['name']]); ?>">
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
                <div>
                    <input type="submit" value="Update">
                </div>
            </form>
            <?php 
                } else {
                    echo "<p>Registro no encontrado.</p>";
                }
            }
            ?>
        <?php elseif ($selectedDb): ?>
            <!-- Show table selection form -->
            <form method="get" class="select-table-form">
                <input type="hidden" name="db" value="<?php echo htmlspecialchars($selectedDb); ?>">
                <label for="table">Selecciona una tabla:</label>
                <select name="table" id="table" onchange="this.form.submit()">
                    <option value="">-- Escoge la tabla --</option>
                    <?php foreach ($tables as $t): ?>
                        <option value="<?php echo htmlspecialchars($t); ?>"
                                <?php echo ($t == $table) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($t); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </form>

            <?php if ($table): ?>
                <!-- The CRUD operations for the selected table are handled above -->
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<!-- (Optional) Additional scripts or styles -->
<link rel="stylesheet" href="https://jocarsa.github.io/jocarsa-snow/jocarsa%20%7C%20snow.css">
<script src="https://jocarsa.github.io/jocarsa-snow/jocarsa%20%7C%20snow.js" defer></script>

<link rel="stylesheet" href="https://jocarsa.github.io/jocarsa-seashell/jocarsa%20%7C%20seashell.css">
<script src="https://jocarsa.github.io/jocarsa-seashell/jocarsa%20%7C%20seashell.js" defer></script>

<!-- Include Chart.js from CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<!-- We'll pass our $chartsData to JS -->
<script>
    window._chartsData = <?php echo json_encode($chartsData, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
</script>

<!-- External JS file that generates the pie charts -->
<script src="charts.js"></script>

</body>
</html>

