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
     * @param array $data Datos de la venta
     * @return string|false El ID de la nueva venta o false si falla.
     */
    public function create($data)
    {
        try {
            $sql = "INSERT INTO ventas (
                        id_paciente, 
                        numero_nota, 
                        numero_nota_sufijo,
                        vendedor_armazon,   
                        fecha_venta, 
                        costo_total, 
                        estado_pago, 
                        observaciones_venta
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            
            $stmt = $this->pdo->prepare($sql);
            
            $stmt->execute([
                $data['id_paciente'],
                $data['numero_nota'],
                $data['numero_nota_sufijo'] ?? null,
                $data['vendedor_armazon'] ?? null,
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
     * Actualiza los datos principales de una venta.
     */
    public function update($id, $data)
    {
        try {
            $sql = "UPDATE ventas SET 
                        numero_nota = ?,
                        vendedor_armazon = ?, -- CAMPO NUEVO
                        fecha_venta = ?,
                        costo_total = ?,
                        observaciones_venta = ?
                    WHERE id_venta = ?";
            
            $stmt = $this->pdo->prepare($sql);
            
            return $stmt->execute([
                $data['numero_nota'],
                $data['vendedor_armazon'] ?? null,
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
     * Elimina una venta y sus registros dependientes (Abonos y Detalles).
     */
    public function delete($id)
    {
        try {
            $this->pdo->beginTransaction();

            $stmt = $this->pdo->prepare("DELETE FROM abonos WHERE id_venta = ?");
            $stmt->execute([(int)$id]);

            $stmt = $this->pdo->prepare("DELETE FROM venta_detalles WHERE id_venta = ?");
            $stmt->execute([(int)$id]);

            $stmt = $this->pdo->prepare("DELETE FROM ventas WHERE id_venta = ?");
            $result = $stmt->execute([(int)$id]);

            $this->pdo->commit();
            return $result;

        } catch (PDOException $e) {
            $this->pdo->rollBack();
            error_log("Error en VentaModel::delete: " . $e->getMessage());
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
     * Obtiene TODAS las ventas registradas (Histórico Completo).
     */
    public function getAll()
    {
        try {
            $sql = "SELECT 
                        v.*, 
                        p.nombre, 
                        p.apellido_paterno, 
                        p.apellido_materno 
                    FROM ventas v
                    LEFT JOIN pacientes p ON v.id_paciente = p.id
                    ORDER BY v.numero_nota DESC";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }

    /**
     * Obtiene las ventas más recientes (limite 50) con datos de paciente.
     */
    public function getAllWithPatient()
    {
        try {
            $sql = "SELECT 
                        v.*, 
                        p.nombre, 
                        p.apellido_paterno, 
                        p.apellido_materno 
                    FROM ventas v
                    LEFT JOIN pacientes p ON v.id_paciente = p.id
                    ORDER BY v.numero_nota DESC
                    LIMIT 50";
            
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
     * Verifica si un número de nota ya existe.
     */
    public function existsNumeroNota($numeroNota)
    {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM ventas WHERE numero_nota = ?");
        $stmt->execute([$numeroNota]);
        return $stmt->fetchColumn() > 0;
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