<?php
/* ==========================================================================
   Modelo para la Gestión de Consultas
   ========================================================================== */

class ConsultaModel
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
     * Obtiene un resumen de las 3 consultas más recientes de un paciente,
     * incluyendo la graduación final (OD y OI) en una sola fila.
     *
     * @param int $patientId El ID del paciente.
     * @return array Una lista de hasta 3 consultas.
     */
    public function getResumenConsultasPorPaciente($patientId)
    {
        try {
            // Esta consulta compleja usa LEFT JOIN y MAX(CASE...) para "pivotar"
            // los datos de graduación (OD y OI) y ponerlos en una sola fila
            // por cada consulta.
            
            $sql = "
                SELECT 
                    c.id AS consulta_id, 
                    c.fecha, 
                    
                    -- Columnas para Ojo Derecho (OD)
                    MAX(CASE WHEN g.ojo = 'OD' THEN g.esfera ELSE NULL END) AS od_esfera,
                    MAX(CASE WHEN g.ojo = 'OD' THEN g.cilindro ELSE NULL END) AS od_cilindro,
                    MAX(CASE WHEN g.ojo = 'OD' THEN g.eje ELSE NULL END) AS od_eje,
                    MAX(CASE WHEN g.ojo = 'OD' THEN g.adicion ELSE NULL END) AS od_adicion,
                    
                    -- Columnas para Ojo Izquierdo (OI)
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
                    c.id, c.fecha
                ORDER BY 
                    c.fecha DESC
                LIMIT 3
            ";

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([(int)$patientId]);
            
            return $stmt->fetchAll();

        } catch (PDOException $e) {
            // En caso de error, devolvemos un array vacío
            error_log("Error en ConsultaModel::getResumenConsultas: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtiene TODAS las consultas de un paciente, incluyendo la
     * graduación final (OD y OI) en una sola fila.
     *
     * @param int $patientId El ID del paciente.
     * @return array Una lista de todas sus consultas.
     */
    public function getAllByPaciente($patientId)
    {
        try {
            // Esta consulta usa LEFT JOIN y MAX(CASE...) para "pivotar"
            // los datos de graduación (OD y OI) y ponerlos en una sola fila.
            
            $sql = "
                SELECT 
                    c.id AS consulta_id, 
                    c.fecha, 
                    
                    -- Columnas para Ojo Derecho (OD)
                    MAX(CASE WHEN g.ojo = 'OD' THEN g.esfera ELSE NULL END) AS od_esfera,
                    MAX(CASE WHEN g.ojo = 'OD' THEN g.cilindro ELSE NULL END) AS od_cilindro,
                    MAX(CASE WHEN g.ojo = 'OD' THEN g.eje ELSE NULL END) AS od_eje,
                    MAX(CASE WHEN g.ojo = 'OD' THEN g.adicion ELSE NULL END) AS od_adicion,
                    
                    -- Columnas para Ojo Izquierdo (OI)
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
                    c.id, c.fecha
                ORDER BY 
                    c.fecha DESC
            "; // (Es la misma consulta que el Resumen, pero sin LIMIT 3)

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([(int)$patientId]);
            
            return $stmt->fetchAll();

        } catch (PDOException $e) {
            error_log("Error en ConsultaModel::getAllByPaciente: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Crea un nuevo registro de consulta.
     *
     * @param array $data Datos de la consulta (patient_id, motivo_consulta, etc.)
     * @return string|false El ID de la nueva consulta insertada, o false si falla.
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
                        observaciones
                    ) VALUES (?, ?, ?, ?, ?, ?)";

            $stmt = $this->pdo->prepare($sql);

            $stmt->execute([
                $data['patient_id'],
                $data['usuario_id'],
                $data['fecha'],
                $data['motivo_consulta'],
                $data['detalle_motivo'],
                $data['observaciones']
            ]);

            // Devolvemos el ID de la consulta que acabamos de crear
            return $this->pdo->lastInsertId();

        } catch (PDOException $e) {
            error_log("Error en ConsultaModel::createConsulta: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtiene una consulta específica por su ID.
     *
     * @param int $consultaId El ID de la consulta.
     * @return array|false Los datos de la consulta o false si no la encuentra.
     */
    public function getConsultaById($consultaId)
    {
        try {
            $sql = "SELECT * FROM consultas WHERE id = ?";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([(int)$consultaId]);
            return $stmt->fetch(); // Devuelve una sola fila

        } catch (PDOException $e) {
            error_log("Error en ConsultaModel::getConsultaById: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Elimina una consulta específica por su ID.
     * (Gracias a 'ON DELETE CASCADE' en la BD, esto también
     * eliminará todas las 'graduaciones' asociadas).
     *
     * @param int $consultaId El ID de la consulta a borrar.
     * @return bool True si el borrado fue exitoso, false si no.
     */
    public function deleteConsulta($consultaId)
    {
        try {
            $sql = "DELETE FROM consultas WHERE id = ?";
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute([(int)$consultaId]);

        } catch (PDOException $e) {
            error_log("Error en ConsultaModel::deleteConsulta: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Actualiza una consulta existente por su ID.
     *
     * @param int $consultaId El ID de la consulta a actualizar.
     * @param array $data Los nuevos datos (fecha, motivo, etc.).
     * @return bool True si la actualización fue exitosa, false si no.
     */
    public function updateConsulta($consultaId, $data)
    {
        try {
            $sql = "UPDATE consultas SET 
                        fecha = ?,
                        motivo_consulta = ?,
                        detalle_motivo = ?,
                        observaciones = ?
                    WHERE id = ?";
            
            $stmt = $this->pdo->prepare($sql);
            
            return $stmt->execute([
                $data['fecha'],
                $data['motivo_consulta'],
                $data['detalle_motivo'],
                $data['observaciones'],
                (int)$consultaId
            ]);

        } catch (PDOException $e) {
            error_log("Error en ConsultaModel::updateConsulta: " . $e->getMessage());
            return false;
        }
    }
}