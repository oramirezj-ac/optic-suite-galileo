<?php
/* ==========================================================================
   Modelo para la Gestión de Graduaciones
   ========================================================================== */

class GraduacionModel
{
    private $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Crea un registro de graduación.
     *
     * @param int $consultaId ID de la consulta a la que pertenece.
     * @param array $data Datos de la graduación (ojo, esfera, cilindro, etc.).
     * @return bool True si tuvo éxito, False si no.
     */
    public function create($consultaId, $data)
    {
        try {
            $sql = "INSERT INTO graduaciones (
                        consulta_id, 
                        tipo, 
                        ojo, 
                        esfera, 
                        cilindro, 
                        eje, 
                        adicion, 
                        observaciones,
                        es_graduacion_final
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
            
            $stmt = $this->pdo->prepare($sql);
            
            // Determinamos si es 'final' basado en el checkbox
            // Si el checkbox 'es_graduacion_final' fue enviado, su valor es '1' (true)
            // Si no fue enviado (null), su valor es '0' (false)
            $es_final = $data['es_graduacion_final'] ?? 0;

            return $stmt->execute([
                $consultaId,
                $data['tipo'] ?? 'final',
                $data['ojo'],
                $data['esfera'],
                $data['cilindro'] ?? 0.00,
                $data['eje'] ?? 0,
                $data['adicion'] ?? 0.00,
                $data['observaciones'] ?? null,
                $es_final // <-- LÍNEA CORREGIDA
            ]);

        } catch (PDOException $e) {
            // Manejo de error
            error_log("Error en GraduacionModel::create: " . $e->getMessage());
            return false;
        }
    }
    /**
     * Obtiene TODAS las graduaciones (Final, Auto, etc.)
     * asociadas a UN ID de consulta.
     *
     * @param int $consultaId El ID de la consulta.
     * @return array Una lista de todas sus graduaciones.
     */
    public function getAllByConsulta($consultaId)
    {
        try {
            $sql = "
                SELECT * FROM graduaciones
                WHERE 
                    consulta_id = ?
                ORDER BY 
                    -- Hacemos que 'final' aparezca primero
                    CASE WHEN tipo = 'final' THEN 1 ELSE 2 END,
                    fecha_hora DESC
            ";

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([(int)$consultaId]);
            
            return $stmt->fetchAll();

        } catch (PDOException $e) {
            error_log("Error en GraduacionModel::getAllByConsulta: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtiene un par de graduaciones (OD y OI) por su 'tipo' 
     * dentro de una consulta específica.
     *
     * @param int $consultaId El ID de la consulta.
     * @param string $tipo El tipo de graduación (ej. 'final', 'lensometro').
     * @return array Un array asociativo ['OD' => [...], 'OI' => [...]].
     */
    public function getByConsultaAndType($consultaId, $tipo)
    {
        $graduaciones = [
            'OD' => null,
            'OI' => null
        ];

        try {
            $sql = "SELECT * FROM graduaciones 
                    WHERE consulta_id = ? 
                    AND tipo = ? 
                    AND (ojo = 'OD' OR ojo = 'OI')";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([(int)$consultaId, $tipo]);
            
            // Agrupamos los resultados
            while ($row = $stmt->fetch()) {
                if ($row['ojo'] === 'OD') {
                    $graduaciones['OD'] = $row;
                } elseif ($row['ojo'] === 'OI') {
                    $graduaciones['OI'] = $row;
                }
            }
            
            return $graduaciones;

        } catch (PDOException $e) {
            error_log("Error en GraduacionModel::getByConsultaAndType: " . $e->getMessage());
            return $graduaciones; // Devuelve el array vacío
        }
    }

    /**
     * Elimina un par de graduaciones (OD y OI) por su 'tipo'
     * dentro de una consulta específica.
     *
     * @param int $consultaId El ID de la consulta.
     * @param string $tipo El tipo de graduación (ej. 'final').
     * @return bool True si el borrado fue exitoso, false si no.
     */
    public function deleteGraduacionByType($consultaId, $tipo)
    {
        try {
            $sql = "DELETE FROM graduaciones 
                    WHERE consulta_id = ? 
                    AND tipo = ?";
            
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute([(int)$consultaId, $tipo]);

        } catch (PDOException $e) {
            error_log("Error en GraduacionModel::deleteGraduacionByType: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Actualiza una fila de graduación existente por su ID.
     *
     * @param int $graduacionId El ID (llave primaria) de la fila a actualizar.
     * @param array $data Los nuevos datos (esfera, cilindro, etc.).
     * @return bool True si la actualización fue exitosa, false si no.
     */
    public function updateGraduacion($graduacionId, $data)
    {
        try {
            // Nota: No actualizamos 'tipo' ni 'ojo'
            $sql = "UPDATE graduaciones SET 
                        esfera = ?,
                        cilindro = ?,
                        eje = ?,
                        adicion = ?
                    WHERE id = ?";
            
            $stmt = $this->pdo->prepare($sql);
            
            return $stmt->execute([
                $data['esfera'],
                $data['cilindro'],
                $data['eje'],
                $data['adicion'],
                (int)$graduacionId
            ]);

        } catch (PDOException $e) {
            error_log("Error en GraduacionModel::updateGraduacion: " . $e->getMessage());
            return false;
        }
    }
}