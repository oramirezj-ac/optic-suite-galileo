<?php
/* ==========================================================================
   Modelo para la Gestión de Consultas (Refractivas y Médicas)
   ========================================================================== */

class ConsultaModel
{
    private $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Obtiene un resumen de las 3 consultas más recientes.
     * (Esta función NO SE TOCA, funciona bien para lentes).
     * UPDATE: Corregido JOIN para usar es_graduacion_final
     */
    public function getResumenConsultasPorPaciente($patientId)
    {
        try {
            $sql = "
                SELECT 
                    c.id AS consulta_id, 
                    c.fecha,
                    c.motivo_consulta,
                    c.dp_lejos_total,
                    c.dp_od,
                    c.dp_oi,
                    c.altura_oblea,
                    MAX(CASE WHEN g.ojo = 'OD' THEN g.esfera ELSE NULL END) AS od_esfera,
                    MAX(CASE WHEN g.ojo = 'OD' THEN g.cilindro ELSE NULL END) AS od_cilindro,
                    MAX(CASE WHEN g.ojo = 'OD' THEN g.eje ELSE NULL END) AS od_eje,
                    MAX(CASE WHEN g.ojo = 'OD' THEN g.adicion ELSE NULL END) AS od_adicion,
                    MAX(CASE WHEN g.ojo = 'OI' THEN g.esfera ELSE NULL END) AS oi_esfera,
                    MAX(CASE WHEN g.ojo = 'OI' THEN g.cilindro ELSE NULL END) AS oi_cilindro,
                    MAX(CASE WHEN g.ojo = 'OI' THEN g.eje ELSE NULL END) AS oi_eje,
                    MAX(CASE WHEN g.ojo = 'OI' THEN g.adicion ELSE NULL END) AS oi_adicion
                FROM 
                    consultas c
                LEFT JOIN 
                    graduaciones g ON c.id = g.consulta_id AND g.es_graduacion_final = 1
                WHERE 
                    c.paciente_id = ?
                GROUP BY 
                    c.id, c.fecha, c.motivo_consulta, c.dp_lejos_total, c.dp_od, c.dp_oi, c.altura_oblea
                ORDER BY 
                    c.fecha DESC
                LIMIT 3
            ";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([(int)$patientId]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }

    /**
     * Obtiene TODAS las consultas de un paciente.
     * UPDATE: Agregamos campos médicos y financieros al SELECT.
     * UPDATE 2: Agregamos filtro opcional por tipo de consulta
     * UPDATE 3: Corregido JOIN para usar es_graduacion_final en lugar de tipo='final'
     * UPDATE 4: Removido metodo_pago (no existe en tabla)
     */
    public function getAllByPaciente($patientId, $tipo = null)
    {
        try {
            $sql = "
                SELECT 
                    c.id AS consulta_id, 
                    c.fecha, 
                    c.motivo_consulta,      -- NECESARIO
                    c.detalle_motivo,       -- NECESARIO
                    c.costo_servicio,       -- NUEVO
                    c.estado_financiero,    -- NUEVO
                    c.diagnostico_dx,       -- NUEVO (Para mostrar si es médica)
                    
                    MAX(CASE WHEN g.ojo = 'OD' THEN g.esfera ELSE NULL END) AS od_esfera,
                    MAX(CASE WHEN g.ojo = 'OD' THEN g.cilindro ELSE NULL END) AS od_cilindro,
                    MAX(CASE WHEN g.ojo = 'OD' THEN g.eje ELSE NULL END) AS od_eje,
                    MAX(CASE WHEN g.ojo = 'OD' THEN g.adicion ELSE NULL END) AS od_adicion,
                    MAX(CASE WHEN g.ojo = 'OI' THEN g.esfera ELSE NULL END) AS oi_esfera,
                    MAX(CASE WHEN g.ojo = 'OI' THEN g.cilindro ELSE NULL END) AS oi_cilindro,
                    MAX(CASE WHEN g.ojo = 'OI' THEN g.eje ELSE NULL END) AS oi_eje,
                    MAX(CASE WHEN g.ojo = 'OI' THEN g.adicion ELSE NULL END) AS oi_adicion
                FROM 
                    consultas c
                LEFT JOIN 
                    graduaciones g ON c.id = g.consulta_id AND g.es_graduacion_final = 1
                WHERE 
                    c.paciente_id = ?";
            
            // Agregar filtro por tipo si se especifica
            $params = [(int)$patientId];
            if ($tipo !== null) {
                $sql .= " AND c.motivo_consulta = ?";
                $params[] = ucfirst($tipo); // 'Refractiva' o 'Médica'
            }
            
            $sql .= "
                GROUP BY 
                    c.id, c.fecha, c.motivo_consulta, c.detalle_motivo, c.costo_servicio, c.estado_financiero, c.diagnostico_dx
                ORDER BY 
                    c.fecha DESC
            ";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }

    /**
     * Crea un nuevo registro de consulta.
     * UPDATE: Soporta campos médicos y financieros.
     */
    public function createConsulta($data)
    {
        try {
            $sql = "INSERT INTO consultas (
                        paciente_id, 
                        usuario_id, 
                        fecha, 
                        motivo_consulta, 
                        detalle_motivo, 
                        observaciones,
                        diagnostico_dx,
                        tratamiento_rx,
                        costo_servicio,
                        estado_financiero,
                        av_ao_id,
                        av_od_id,
                        av_oi_id,
                        cv_ao_id,
                        cv_od_id,
                        cv_oi_id,
                        dp_lejos_total,
                        dp_od,
                        dp_oi,
                        altura_oblea
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

            $stmt = $this->pdo->prepare($sql);

            $stmt->execute([
                $data['patient_id'],
                $data['usuario_id'],
                $data['fecha'],
                $data['motivo_consulta'],
                $data['detalle_motivo'] ?? null,
                $data['observaciones'] ?? null,
                // Campos médicos
                $data['diagnostico_dx'] ?? null,
                $data['tratamiento_rx'] ?? null,
                $data['costo_servicio'] ?? 0.00,
                $data['estado_financiero'] ?? 'cobrado',
                // Agudeza Visual
                $data['av_ao_id'] ?? null,
                $data['av_od_id'] ?? null,
                $data['av_oi_id'] ?? null,
                // Corrección Visual
                $data['cv_ao_id'] ?? null,
                $data['cv_od_id'] ?? null,
                $data['cv_oi_id'] ?? null,
                // Distancia Pupilar
                $data['dp_lejos_total'] ?? null,
                $data['dp_od'] ?? null,
                $data['dp_oi'] ?? null,
                $data['altura_oblea'] ?? null
            ]);

            return $this->pdo->lastInsertId();

        } catch (PDOException $e) {
            error_log("Error en ConsultaModel::createConsulta: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Actualiza una consulta existente.
     * UPDATE: Soporta campos médicos, financieros y DP.
     */
    public function updateConsulta($consultaId, $data)
    {
        try {
            $sql = "UPDATE consultas SET 
                        fecha = ?,
                        motivo_consulta = ?,
                        detalle_motivo = ?,
                        observaciones = ?,
                        diagnostico_dx = ?,
                        tratamiento_rx = ?,
                        costo_servicio = ?,
                        estado_financiero = ?,
                        metodo_pago = ?,
                        dp_lejos_total = ?,
                        dp_od = ?,
                        dp_oi = ?,
                        dp_cerca = ?
                    WHERE id = ?";
            
            $stmt = $this->pdo->prepare($sql);
            
            return $stmt->execute([
                $data['fecha'] ?? null,
                $data['motivo_consulta'] ?? null,
                $data['detalle_motivo'] ?? null,
                $data['observaciones'] ?? null,
                // Campos médicos
                $data['diagnostico_dx'] ?? null,
                $data['tratamiento_rx'] ?? null,
                $data['costo_servicio'] ?? 0.00,
                $data['estado_financiero'] ?? 'cobrado',
                $data['metodo_pago'] ?? null,
                // Campos DP
                $data['dp_lejos_total'] ?? null,
                $data['dp_od'] ?? null,
                $data['dp_oi'] ?? null,
                $data['dp_cerca'] ?? null,
                (int)$consultaId
            ]);

        } catch (PDOException $e) {
            error_log("Error en ConsultaModel::updateConsulta: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Actualiza solo campos específicos de una consulta (UPDATE parcial).
     * Útil para actualizar solo DP sin afectar otros campos.
     */
    public function updateConsultaPartial($consultaId, $data)
    {
        try {
            if (empty($data)) {
                return false;
            }

            $fields = [];
            $values = [];
            
            foreach ($data as $key => $value) {
                $fields[] = "$key = ?";
                $values[] = $value;
            }
            
            $values[] = (int)$consultaId;
            
            $sql = "UPDATE consultas SET " . implode(', ', $fields) . " WHERE id = ?";
            $stmt = $this->pdo->prepare($sql);
            
            return $stmt->execute($values);

        } catch (PDOException $e) {
            error_log("Error en ConsultaModel::updateConsultaPartial: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtiene una consulta por ID.
     */
    public function getConsultaById($consultaId)
    {
        try {
            $sql = "SELECT * FROM consultas WHERE id = ?";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([(int)$consultaId]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Elimina una consulta.
     */
    public function deleteConsulta($consultaId)
    {
        try {
            $sql = "DELETE FROM consultas WHERE id = ?";
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute([(int)$consultaId]);
        } catch (PDOException $e) {
            return false;
        }
    }

    // --- FUNCIONES ESPECÍFICAS (NO SE TOCAN, SE MANTIENEN IGUAL) ---

    public function updateBiometria($id, $data)
    {
        try {
            $sql = "UPDATE consultas SET 
                        dp_lejos_total = ?, dp_od = ?, dp_oi = ?, altura_oblea = ?
                    WHERE id = ?";
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute([
                $data['dp_lejos_total'] ?: null,
                $data['dp_od'] ?: null,
                $data['dp_oi'] ?: null,
                $data['altura_oblea'] ?: null,
                (int)$id
            ]);
        } catch (PDOException $e) {
            return false;
        }
    }

    public function getCatalogoAV()
    {
        try {
            $stmt = $this->pdo->query("SELECT * FROM catalogo_agudeza_visual ORDER BY id ASC");
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }

    public function updateDatosClinicos($id, $data)
    {
        try {
            $sql = "UPDATE consultas SET 
                        av_ao_id = ?, av_od_id = ?, av_oi_id = ?,
                        cv_ao_id = ?, cv_od_id = ?, cv_oi_id = ?
                    WHERE id = ?";
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute([
                $data['av_ao_id'] ?: null,
                $data['av_od_id'] ?: null,
                $data['av_oi_id'] ?: null,
                $data['cv_ao_id'] ?: null,
                $data['cv_od_id'] ?: null,
                $data['cv_oi_id'] ?: null,
                (int)$id
            ]);
        } catch (PDOException $e) {
            return false;
        }
    }

    // ========================================================================
    // MÉTODOS PARA EL WIZARD DE CONSULTAS NUEVAS
    // ========================================================================

    /**
     * Crea una consulta completa con todas sus graduaciones y productos médicos.
     * Usa transacción para garantizar integridad de datos.
     * 
     * @param array $consultaData Datos de la consulta
     * @param array $graduaciones Array de graduaciones a crear
     * @param array $productosMedicos Array de productos médicos (opcional)
     * @return int|false ID de la consulta creada o false si falla
     */
    public function createConsultaCompleta($consultaData, $graduaciones = [], $productosMedicos = [])
    {
        try {
            $this->pdo->beginTransaction();
            
            // 1. Crear consulta
            $consultaId = $this->createConsulta($consultaData);
            
            if (!$consultaId) {
                throw new Exception("Error al crear consulta");
            }
            
            // 2. Crear graduaciones
            if (!empty($graduaciones)) {
                require_once __DIR__ . '/GraduacionModel.php';
                $graduacionModel = new GraduacionModel($this->pdo);
                
                foreach ($graduaciones as $grad) {
                    $result = $graduacionModel->create($consultaId, $grad);
                    if (!$result) {
                        throw new Exception("Error al crear graduación");
                    }
                }
            }
            
            // 3. Agregar productos médicos (si aplica)
            if (!empty($productosMedicos)) {
                $this->addProductosMedicos($consultaId, $productosMedicos);
            }
            
            $this->pdo->commit();
            return $consultaId;
            
        } catch (Exception $e) {
            $this->pdo->rollBack();
            error_log("Error en createConsultaCompleta: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtiene el catálogo de productos médicos activos.
     * 
     * @return array Lista de productos médicos
     */
    public function getCatalogoProductosMedicos()
    {
        try {
            $sql = "SELECT * FROM catalogo_productos_medicos WHERE activo = 1 ORDER BY nombre";
            $stmt = $this->pdo->query($sql);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error en getCatalogoProductosMedicos: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Agrega productos médicos a una consulta.
     * 
     * @param int $consultaId ID de la consulta
     * @param array $productos Array de productos con estructura: [producto_id, cantidad, precio]
     * @return bool True si tuvo éxito, false si no
     */
    private function addProductosMedicos($consultaId, $productos)
    {
        try {
            $sql = "INSERT INTO consulta_productos_medicos 
                    (consulta_id, producto_id, cantidad, precio) 
                    VALUES (?, ?, ?, ?)";
            $stmt = $this->pdo->prepare($sql);
            
            foreach ($productos as $prod) {
                $stmt->execute([
                    $consultaId,
                    $prod['producto_id'],
                    $prod['cantidad'] ?? 1,
                    $prod['precio'] ?? 0.00
                ]);
            }
            
            return true;
        } catch (PDOException $e) {
            error_log("Error en addProductosMedicos: " . $e->getMessage());
            throw $e; // Re-lanzar para que la transacción haga rollback
        }
    }


    /**
     * Obtiene los productos médicos de una consulta.
     * 
     * @param int $consultaId ID de la consulta
     * @return array Lista de productos médicos de la consulta
     */
    public function getProductosMedicosByConsulta($consultaId)
    {
        try {
            $sql = "SELECT 
                        cpm.*,
                        pm.nombre as producto_nombre
                    FROM consulta_productos_medicos cpm
                    INNER JOIN catalogo_productos_medicos pm ON cpm.producto_id = pm.id
                    WHERE cpm.consulta_id = ?
                    ORDER BY cpm.id";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([(int)$consultaId]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error en getProductosMedicosByConsulta: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Elimina todos los productos médicos de una consulta.
     * 
     * @param int $consultaId ID de la consulta
     * @return bool True si tuvo éxito, false si no
     */
    public function deleteProductosMedicosByConsulta($consultaId)
    {
        try {
            $sql = "DELETE FROM consulta_productos_medicos WHERE consulta_id = ?";
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute([(int)$consultaId]);
        } catch (PDOException $e) {
            error_log("Error en deleteProductosMedicosByConsulta: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Actualiza los productos médicos de una consulta.
     * Elimina los productos anteriores y agrega los nuevos.
     * 
     * @param int $consultaId ID de la consulta
     * @param array $productos Array de productos con estructura: [producto_id, cantidad, precio]
     * @return bool True si tuvo éxito, false si no
     */
    public function updateProductosMedicos($consultaId, $productos)
    {
        try {
            $this->pdo->beginTransaction();
            
            // 1. Eliminar productos anteriores
            $this->deleteProductosMedicosByConsulta($consultaId);
            
            // 2. Agregar nuevos productos (si hay)
            if (!empty($productos)) {
                $this->addProductosMedicos($consultaId, $productos);
            }
            
            $this->pdo->commit();
            return true;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            error_log("Error en updateProductosMedicos: " . $e->getMessage());
            return false;
        }
    }
}
