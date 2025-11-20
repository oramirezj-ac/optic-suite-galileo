<?php
/* ==========================================================================
   Modelo para la Gestión de Ventas (Encabezado de la Nota)
   ========================================================================== */

class VentaModel
{
    private $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Crea un nuevo encabezado de venta.
     * * @param array $data Datos de la venta (id_paciente, numero_nota, total, etc.)
     * @return string|false El ID de la nueva venta o false si falla.
     */
    public function create($data)
    {
        try {
            $sql = "INSERT INTO ventas (
                        id_paciente, 
                        numero_nota, 
                        numero_nota_sufijo,
                        fecha_venta, 
                        costo_total, 
                        estado_pago, 
                        observaciones_venta
                    ) VALUES (?, ?, ?, ?, ?, ?, ?)";
            
            $stmt = $this->pdo->prepare($sql);
            
            $stmt->execute([
                $data['id_paciente'],
                $data['numero_nota'],
                $data['numero_nota_sufijo'] ?? null, // El sufijo opcional (-A, -D)
                $data['fecha_venta'],
                $data['costo_total'],
                $data['estado_pago'] ?? 'pendiente',
                $data['observaciones'] ?? null
            ]);

            return $this->pdo->lastInsertId();

        } catch (PDOException $e) {
            error_log("Error en VentaModel::create: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtiene una venta específica por su ID.
     */
    public function getById($id)
    {
        try {
            $stmt = $this->pdo->prepare("SELECT * FROM ventas WHERE id_venta = ?");
            $stmt->execute([(int)$id]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Obtiene todas las ventas de un paciente (para la pestaña de historial).
     */
    public function getAllByPaciente($patientId)
    {
        try {
            $sql = "SELECT * FROM ventas WHERE id_paciente = ? ORDER BY fecha_venta DESC";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([(int)$patientId]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }

    /**
     * Verifica si un número de nota ya existe.
     * @return bool True si existe, False si está libre.
     */
    public function existsNumeroNota($numeroNota)
    {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM ventas WHERE numero_nota = ?");
        $stmt->execute([$numeroNota]);
        return $stmt->fetchColumn() > 0;
    }

    /**
     * Elimina una venta y sus registros dependientes (Abonos y Detalles).
     * Usa una transacción para asegurar que se borre todo o nada.
     * * @param int $id El ID de la venta.
     * @return bool
     */
    public function delete($id)
    {
        try {
            // 1. Iniciamos una transacción (Modo seguro)
            $this->pdo->beginTransaction();

            // 2. Borramos los ABONOS (Hijos)
            $stmt = $this->pdo->prepare("DELETE FROM abonos WHERE id_venta = ?");
            $stmt->execute([(int)$id]);

            // 3. Borramos los DETALLES DE PRODUCTO (Hijos)
            // (Aunque tu tabla venta_detalles tenga cascade, hacerlo explícito no daña y asegura el borrado)
            $stmt = $this->pdo->prepare("DELETE FROM venta_detalles WHERE id_venta = ?");
            $stmt->execute([(int)$id]);

            // 4. Finalmente, borramos la VENTA (Padre)
            $stmt = $this->pdo->prepare("DELETE FROM ventas WHERE id_venta = ?");
            $result = $stmt->execute([(int)$id]);

            // 5. Confirmamos los cambios
            $this->pdo->commit();
            
            return $result;

        } catch (PDOException $e) {
            // Si algo falló, regresamos el tiempo atrás (Deshacer cambios)
            $this->pdo->rollBack();
            error_log("Error en VentaModel::delete: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Actualiza los datos principales de una venta.
     *
     * @param int $id El ID de la venta.
     * @param array $data Nuevos datos (numero_nota, fecha, total, observaciones).
     * @return bool
     */
    public function update($id, $data)
    {
        try {
            $sql = "UPDATE ventas SET 
                        numero_nota = ?,
                        fecha_venta = ?,
                        costo_total = ?,
                        observaciones_venta = ?
                    WHERE id_venta = ?";
            
            $stmt = $this->pdo->prepare($sql);
            
            return $stmt->execute([
                $data['numero_nota'],
                $data['fecha_venta'],
                $data['costo_total'],
                $data['observaciones'],
                (int)$id
            ]);

        } catch (PDOException $e) {
            error_log("Error en VentaModel::update: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtiene las ventas más recientes (limite 50) uniendo datos del paciente.
     * Ordenado por Número de Nota descendente (las más nuevas arriba).
     */
    public function getAllWithPatient()
    {
        try {
            // Hacemos JOIN para obtener el nombre del paciente en la misma consulta
            $sql = "SELECT 
                        v.*, 
                        p.nombre, 
                        p.apellido_paterno, 
                        p.apellido_materno 
                    FROM ventas v
                    LEFT JOIN pacientes p ON v.id_paciente = p.id
                    ORDER BY v.numero_nota DESC
                    LIMIT 50"; // Límite inicial para rendimiento
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }

    /**
     * Busca ventas por coincidencia en Nombre, Apellidos o Número de Nota.
     */
    public function searchByTerm($term)
    {
        try {
            $sql = "SELECT 
                        v.*, 
                        p.nombre, 
                        p.apellido_paterno, 
                        p.apellido_materno 
                    FROM ventas v
                    LEFT JOIN pacientes p ON v.id_paciente = p.id
                    WHERE 
                        v.numero_nota LIKE ? OR 
                        p.nombre LIKE ? OR 
                        p.apellido_paterno LIKE ? OR 
                        p.apellido_materno LIKE ?
                    ORDER BY v.numero_nota DESC";
            
            $term = "%$term%";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$term, $term, $term, $term]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }

    /**
     * Busca ventas dentro de un rango de fechas específico.
     */
    public function searchByDateRange($start, $end)
    {
        try {
            // Añadimos horas para cubrir el día completo
            $start = $start . ' 00:00:00';
            $end = $end . ' 23:59:59';

            $sql = "SELECT 
                        v.*, 
                        p.nombre, 
                        p.apellido_paterno, 
                        p.apellido_materno 
                    FROM ventas v
                    LEFT JOIN pacientes p ON v.id_paciente = p.id
                    WHERE v.fecha_venta BETWEEN ? AND ?
                    ORDER BY v.fecha_venta DESC";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$start, $end]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }

    /**
     * Actualiza solo el estado de pago de una venta.
     */
    public function updateStatus($id, $estado)
    {
        try {
            $sql = "UPDATE ventas SET estado_pago = ? WHERE id_venta = ?";
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute([$estado, (int)$id]);
        } catch (PDOException $e) {
            return false;
        }
    }
}