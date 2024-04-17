<!DOCTYPE html>
<html>
<head>
    <title>Pagos y Usuarios</title>
    <script>
    function mostrarAlerta(mensaje) {
        alert(mensaje);
    }
    </script>
</head>
<body>
    <h1>Pagos y Usuarios</h1>
    
    <!-- Formulario para consignar o retirar dinero -->
    <h2>Registrar compra</h2>
    <form method="post">
        <label for="cuenta_id">Numero de la Cuenta o Tarjeta:</label>
        <input type="text" name="cuenta_id" id="cuenta_id" required><br><br>
        
        <label for="monto">Monto:</label>
        <input type="text" name="monto" id="monto" required><br><br>
        
        <input type="submit" name="realizar_operacion" value="Realizar Operación">
    </form>

    <!-- Botón para ver la lista de usuarios -->
    <h2>Ver Compras</h2>
    <form method="post">
        <input type="submit" name="ver_compras" value="Ver Compras">
    </form>

    <?php
    // Configuración de la base de datos
    $servername1 = "172.18.46.209"; // Cambia esto a la dirección de tu servidor MySQL
    $username1 = "espasadena";
    $password1 = "espasadena";
    $dbname = "pagos";

    // Crear conexiones a la base de datos
    $conn1 = new mysqli($servername1, $username1, $password1, $dbname);

    // Verificar las conexiones
    if ($conn1->connect_error) {
        die("Error de conexión: " . $conn1->connect_error);
    }

    // Función para ver la lista de usuarios
    function verCuentas() {
        global $conn1;


        $query = "SELECT U.ID, U.nombreu, U.apellido, M.IDMed, M.nombre, M.num, 
        C.ID_com, C.valor, C.estado, C.descripcion 
        FROM usuarios AS U, MedioPago AS M, compras AS C WHERE (U.ID = M.ID_usr) AND (C.ID_md = M.IDMed);";
        $result = $conn1->query($query);

        if ($result->num_rows > 0) {
            echo "<h2>Lista de Usuarios</h2>";
            echo "<table border='1'>";
            echo "<tr>
                    <th>ID usuario</th>
                    <th>Nombre</th>
                    <th>ID Medio de Pago</th>
                    <th>Medio de Pago</th>
                    <th>Numero de tarjeta/cuenta</th>
                    <th>ID Compra</th>
                    <th>Valor Compra</th>
                    <th>Estado</th>
                    <th>Descripcion</th>
                </tr>";

            while ($row = $result->fetch_assoc()) {
                echo "<tr>";
                echo "<td>" . $row["ID"] . "</td>";
                echo "<td>" . $row["nombreu"] . " ". $row["apellido"] . "</td>";
                echo "<td>" . $row["IDMed"] . "</td>";
                echo "<td>" . $row["nombre"] . "</td>";
                echo "<td>" . $row["num"] . "</td>";
                echo "<td>" . $row["ID_com"] . "</td>";
                echo "<td>" . $row["valor"] . "</td>";
                echo "<td>" . $row["estado"] . "</td>";
                echo "<td>" . $row["descripcion"] . "</td>";
                echo "</tr>";
            }

            echo "</table>";
        } else {
            echo "No se encontraron registros.";
        }

        
    }

    // Procesar el formulario para consignar o retirar dinero
    if (isset($_POST['realizar_operacion'])) {
        $cuenta_id = $_POST['cuenta_id'];
        $monto = $_POST['monto'];

        // Verificar si la cuenta existe en ambas conexiones
        $cuenta_check_query = "SELECT IDMed FROM MedioPago WHERE  num = $cuenta_id";
        $cuenta_check_result1 = $conn1->query($cuenta_check_query);

        if ($cuenta_check_result1->num_rows > 0 ) {
            // La cuenta existe en ambas conexiones

            // Verificar el saldo suficiente antes de realizar el la resta
            
            $query = "SELECT saldo, IDMed FROM MedioPago WHERE num = $cuenta_id";
            $result = $conn1->query($query);
            

            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                $saldo_actual = $row["saldo"];
                $ID_mediopago = $row["IDMed"];
                $query = "SELECT ID_com FROM compras";
                $result = $conn1->query($query);
                $new_id = $result->num_rows +1;

                if ($monto <= $saldo_actual) {
                     // Realizar compra
                    $nuevo_saldo = $saldo_actual - $monto;
                    
                    $realize_compra = "INSERT INTO compras VALUES ($new_id, $monto, 'Exitoso', 'Pago realizado exitosamente', $ID_mediopago );";
                    $update_query = "UPDATE MedioPago SET saldo = $nuevo_saldo WHERE num = $cuenta_id";

                    if ($conn1->query($realize_compra) === TRUE && $conn1->query($update_query) === TRUE) {
                        echo '<script>mostrarAlerta("Pago exitoso. Nuevo saldo: " + ' . $nuevo_saldo . ');</script>';
                        } else {
                            echo "Error al realizar la compra.";
                        }
                    } else {
                        $realize_compra = "INSERT INTO compras VALUES ($new_id, $monto, 'Error', 'Saldo insuficiente', $ID_mediopago );";
                        $conn1->query($realize_compra);
                        echo '<script>mostrarAlerta("Saldo insuficiente para realizar el retiro.");</script>';
                    }
                }else{
                    
                }
            
    
          

          
        } else {
            echo '<script>mostrarAlerta("La cuenta no existe.");</script>';
        }
    }

    // Procesar el formulario para ver usuarios
    if (isset($_POST['ver_compras'])) {
        verCuentas();
    }

    // Cerrar las conexiones a la base de datos
    $conn1->close();
    ?>
</body>
</html>
