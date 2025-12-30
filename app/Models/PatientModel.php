<?php
/* ==========================================================================
   Modelo para la Gestión de Pacientes (Lógica de Base de Datos)
   ========================================================================== */

class PatientModel
{
    /**
     * @var PDO La conexión a la base de datos
     */
    private $pdo;

    /**
     * Constructor. Recibe la conexión PDO.
     * @param PDO $pdo
     */
    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Obtiene todos los pacientes o busca por término.
     * @param string $searchTerm
     * @return array
     */
    public function getAll($searchTerm = '')
    {
        if (!empty($searchTerm)) {
            $stmt = $this->pdo->prepare(
                "SELECT * FROM pacientes 
                 WHERE CONCAT(nombre, ' ', apellido_paterno) LIKE ? OR telefono LIKE ? 
                 ORDER BY apellido_paterno ASC, apellido_materno ASC, nombre ASC"
            );
            $stmt->execute(['%' . $searchTerm . '%', '%' . $searchTerm . '%']);
        } else {
            $stmt = $this->pdo->prepare(
                "SELECT * FROM pacientes 
                 ORDER BY apellido_paterno ASC, apellido_materno ASC, nombre ASC LIMIT 50"
            );
            $stmt->execute();
        }
        return $stmt->fetchAll();
    }

    /**
     * Crea un nuevo paciente.
     * @param array $data Datos del paciente.
     * @return string|false El ID del nuevo paciente si tuvo éxito, False si no.
     */
    public function create($data)
    {
        try {
            // SQL Actualizado: Cambiamos 'edad' por 'fecha_nacimiento' y 'fecha_primera_visita'
            $sql = "INSERT INTO pacientes (
                        nombre, 
                        apellido_paterno, 
                        apellido_materno, 
                        fecha_nacimiento, 
                        fecha_primera_visita, 
                        domicilio, 
                        telefono, 
                        antecedentes_medicos
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            
            $stmt = $this->pdo->prepare($sql);
            
            $success = $stmt->execute([
                $data['nombre'],
                $data['apellido_paterno'],
                $data['apellido_materno'],
                $data['fecha_nacimiento'] ?: null,      // Guardamos fecha o NULL
                $data['fecha_primera_visita'] ?: null,  // Guardamos fecha o NULL
                $data['domicilio'],
                $data['telefono'],
                $data['antecedentes_medicos']
            ]);

            if ($success) {
                return $this->pdo->lastInsertId();
            }
            return false;
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Actualiza un paciente existente preservando la fecha de visita más antigua.
     */
    public function update($id, $data)
    {
        try {
            // 1. RECUPERAR DATOS ACTUALES
            // Consultamos el paciente actual para ver su fecha original
            $pacienteActual = $this->getById($id);
            $fechaOriginal = $pacienteActual['fecha_primera_visita'] ?? null;
            $fechaNueva = $data['fecha_primera_visita'] ?? null;

            // 2. LÓGICA DE ANTIGÜEDAD (El dato más antiguo gana)
            // Definimos cuál fecha final vamos a guardar
            $fechaFinal = $fechaNueva;

            if ($fechaOriginal && $fechaNueva) {
                // Si existen ambas, comparamos strings (YYYY-MM-DD funciona perfecto así)
                if ($fechaOriginal < $fechaNueva) {
                    $fechaFinal = $fechaOriginal; // Gana la original (2020)
                }
            } elseif ($fechaOriginal && empty($fechaNueva)) {
                // Si no mandaron fecha nueva, conservamos la original
                $fechaFinal = $fechaOriginal;
            }
            // (Si no había original, se queda la nueva por defecto)


            // 3. PREPARAR SQL (Ahora es más limpio, ya decidimos la fecha en PHP)
            $sql = "UPDATE pacientes SET 
                        nombre = ?, 
                        apellido_paterno = ?, 
                        apellido_materno = ?, 
                        fecha_nacimiento = ?,
                        fecha_primera_visita = ?, 
                        domicilio = ?, 
                        telefono = ?, 
                        antecedentes_medicos = ? 
                    WHERE id = ?";
            
            $stmt = $this->pdo->prepare($sql);
            
            return $stmt->execute([
                $data['nombre'],
                $data['apellido_paterno'],
                $data['apellido_materno'],
                $data['fecha_nacimiento'] ?: null,
                $fechaFinal, // <--- Aquí va la fecha ganadora
                $data['domicilio'],
                $data['telefono'],
                $data['antecedentes_medicos'],
                (int)$id
            ]);

        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Elimina un paciente y TODO su historial asociado.
     * Realiza un borrado en cascada manual para limpiar:
     * Abonos -> Detalles Venta -> Ventas -> Graduaciones -> Consultas -> Paciente.
     *
     * @param int $id ID del paciente.
     * @return bool True si tuvo éxito.
     */
    public function delete($id)
    {
        try {
            $this->pdo->beginTransaction();

            // 1. LIMPIEZA DE VENTAS
            // Borrar Abonos de las ventas de este paciente
            $this->pdo->prepare("DELETE FROM abonos WHERE id_venta IN (SELECT id_venta FROM ventas WHERE id_paciente = ?)")->execute([$id]);
            
            // Borrar Detalles de productos de las ventas
            $this->pdo->prepare("DELETE FROM venta_detalles WHERE id_venta IN (SELECT id_venta FROM ventas WHERE id_paciente = ?)")->execute([$id]);
            
            // Borrar las Ventas (Encabezados)
            $this->pdo->prepare("DELETE FROM ventas WHERE id_paciente = ?")->execute([$id]);


            // 2. LIMPIEZA CLÍNICA
            // Borrar Graduaciones de las consultas de este paciente
            $this->pdo->prepare("DELETE FROM graduaciones WHERE consulta_id IN (SELECT id FROM consultas WHERE paciente_id = ?)")->execute([$id]);

            // Borrar las Consultas
            $this->pdo->prepare("DELETE FROM consultas WHERE paciente_id = ?")->execute([$id]);


            // 3. BORRADO DEL PACIENTE
            $stmt = $this->pdo->prepare("DELETE FROM pacientes WHERE id = ?");
            $result = $stmt->execute([$id]);

            $this->pdo->commit();
            return $result;

        } catch (PDOException $e) {
            $this->pdo->rollBack();
            error_log("Error en PatientModel::delete: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtiene un paciente específico por su ID.
     * @param int $id
     * @return array|false
     */
    public function getById($id)
    {
        $id = (int) $id;
        try {
            $stmt = $this->pdo->prepare("SELECT * FROM pacientes WHERE id = ?");
            $stmt->execute([$id]);
            return $stmt->fetch(); // Devuelve un solo array o false si no lo encuentra
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Busca pacientes similares por teléfono o nombre.
     * Esta es la "puerta de control" para evitar duplicados.
     *
     * @param array $data Datos del paciente (telefono, nombre, apellido_paterno)
     * @return array Una lista de pacientes duplicados encontrados.
     */
    public function findSimilar($data)
    {
        // Preparamos los datos para que sean seguros
        $nombre = $data['nombre'] ?? '';
        $ap_paterno = $data['apellido_paterno'] ?? '';
        $telefono = $data['telefono'] ?? '';
        
        $duplicates = []; // Aquí guardaremos los resultados únicos

        /*
         * Búsqueda 1: Por Teléfono (Alta Confianza)
         * Si el teléfono existe y no está vacío, es la coincidencia más fuerte.
         */
        if (!empty($telefono)) {
            $stmt = $this->pdo->prepare("SELECT * FROM pacientes WHERE telefono = ?");
            $stmt->execute([$telefono]);
            while ($row = $stmt->fetch()) {
                // Usamos el ID como clave del array para que no se repitan
                $duplicates[$row['id']] = $row; 
            }
        }

        /*
         * Búsqueda 2: Por Nombre (Media Confianza)
         * Para el caso "Dulce" vs "Dulce Paola".
         * Buscamos por Apellido Paterno Y la primera palabra del Nombre.
         */
        if (!empty($nombre) && !empty($ap_paterno)) {
            
            // Obtenemos solo la primera palabra del nombre
            $primer_nombre = explode(' ', $nombre)[0];
            
            $stmt = $this->pdo->prepare(
                "SELECT * FROM pacientes WHERE 
                 apellido_paterno = ? AND 
                 nombre LIKE ?"
            );
            
            // Buscamos 'Dulce%' para que coincida con "Dulce", "Dulce Paola", "Dulce María", etc.
            $stmt->execute([$ap_paterno, $primer_nombre . '%']);
            
            while ($row = $stmt->fetch()) {
                $duplicates[$row['id']] = $row; // Añade al array, sobrescribiendo si ya estaba
            }
        }
        
        // Devolvemos la lista final de duplicados únicos
        return array_values($duplicates);
    }

    /**
     * Obtiene los 10 pacientes modificados más recientemente.
     * Ideal para la pestaña "Recientes".
     */
    public function getRecientes()
    {
        try {
            // Ordenamos por fecha_actualizacion descendente para ver lo último que tocaste
            $stmt = $this->pdo->prepare("SELECT * FROM pacientes ORDER BY fecha_actualizacion DESC LIMIT 10");
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }

    /**
     * Obtiene los N pacientes modificados más recientemente.
     * @param int $limit Número de pacientes a obtener
     * @return array
     */
    public function getRecentPatients($limit = 5)
    {
        try {
            $stmt = $this->pdo->prepare("SELECT * FROM pacientes ORDER BY fecha_actualizacion DESC LIMIT ?");
            $stmt->execute([$limit]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }

    /**
     * Obtiene el listado completo de pacientes (con un límite de seguridad).
     * Ordenado alfabéticamente.
     */
    public function getAllPacientes()
    {
        try {
            // Límite de 2000 para seguridad del navegador
            $stmt = $this->pdo->prepare("SELECT * FROM pacientes ORDER BY apellido_paterno ASC, apellido_materno ASC, nombre ASC LIMIT 2000");
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }

    /**
     * Busca pacientes filtrando por su fecha de primera visita (Alta).
     */
    public function searchByDateRange($start, $end)
    {
        try {
            $sql = "SELECT * FROM pacientes 
                    WHERE fecha_primera_visita BETWEEN ? AND ? 
                    ORDER BY fecha_primera_visita DESC";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$start, $end]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }

    /**
     * Obtiene los años disponibles en el historial de ventas.
     * Usado para el filtro de la pestaña "Auditoría".
     */
    public function getYearsWithSales()
    {
        try {
            $stmt = $this->pdo->query("SELECT DISTINCT YEAR(fecha_venta) as anio FROM ventas ORDER BY anio DESC");
            return $stmt->fetchAll(PDO::FETCH_COLUMN);
        } catch (PDOException $e) {
            return [];
        }
    }

    /**
     * Obtiene la lista de auditoría para organizar expedientes físicos.
     * Filtra por Año de Venta y Letra Inicial del Apellido Paterno.
     */
    public function getAuditList($year, $letter)
    {
        try {
            $sql = "SELECT 
                        p.id,
                        p.nombre, 
                        p.apellido_paterno, 
                        p.apellido_materno,
                        v.numero_nota
                    FROM ventas v
                    INNER JOIN pacientes p ON v.id_paciente = p.id
                    WHERE YEAR(v.fecha_venta) = ? 
                      AND p.apellido_paterno LIKE ?
                    ORDER BY 
                        p.apellido_paterno ASC, 
                        p.apellido_materno ASC, 
                        p.nombre ASC,
                        v.numero_nota ASC";
            
            $stmt = $this->pdo->prepare($sql);
            // Agregamos el comodín '%' para que busque todo lo que empiece con esa letra
            $stmt->execute([$year, $letter . '%']);
            
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }
}