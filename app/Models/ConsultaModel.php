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
     */
    public function getResumenConsultasPorPaciente($patientId)
    {
        try {
            $sql = "
                SELECT 
                    c.id AS consulta_id, 
                    c.fecha, 
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
                    graduaciones g ON c.id = g.consulta_id AND g.tipo = 'final'
                WHERE 
                    c.paciente_id = ?
                GROUP BY 
                    c.id, c.fecha, c.dp_lejos_total, c.dp_od, c.dp_oi, c.altura_oblea
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
     */
    public function getAllByPaciente($patientId)
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
                    graduaciones g ON c.id = g.consulta_id AND g.tipo = 'final'
                WHERE 
                    c.paciente_id = ?
                GROUP BY 
                    c.id, c.fecha, c.motivo_consulta, c.detalle_motivo, c.costo_servicio, c.estado_financiero, c.diagnostico_dx
                ORDER BY 
                    c.fecha DESC
            ";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([(int)$patientId]);
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
                        estado_financiero
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

            $stmt = $this->pdo->prepare($sql);

            $stmt->execute([
                $data['patient_id'],
                $data['usuario_id'],
                $data['fecha'],
                $data['motivo_consulta'],
                $data['detalle_motivo'],
                $data['observaciones'],
                // Nuevos Campos (Opcionales / NULL)
                $data['diagnostico_dx'] ?? null,
                $data['tratamiento_rx'] ?? null,
                $data['costo_servicio'] ?? 0.00,
                $data['estado_financiero'] ?? 'cobrado'
            ]);

            return $this->pdo->lastInsertId();

        } catch (PDOException $e) {
            error_log("Error en ConsultaModel::createConsulta: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Actualiza una consulta existente.
     * UPDATE: Soporta campos médicos y financieros.
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
                        estado_financiero = ?
                    WHERE id = ?";
            
            $stmt = $this->pdo->prepare($sql);
            
            return $stmt->execute([
                $data['fecha'],
                $data['motivo_consulta'],
                $data['detalle_motivo'],
                $data['observaciones'],
                // Nuevos Campos
                $data['diagnostico_dx'] ?? null,
                $data['tratamiento_rx'] ?? null,
                $data['costo_servicio'] ?? 0.00,
                $data['estado_financiero'] ?? 'cobrado',
                (int)$consultaId
            ]);

        } catch (PDOException $e) {
            error_log("Error en ConsultaModel::updateConsulta: " . $e->getMessage());
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
}