<?php
/* ==========================================================================
   Modelo para la Gestión de Abonos (Pagos)
   ========================================================================== */

class AbonoModel
{
    private $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Registra un nuevo abono/pago para una venta.
     *
     * @param array $data Datos del abono (id_venta, monto, fecha).
     * @return bool True si se guardó, False si falló.
     */
    public function create($data)
    {
        try {
            $sql = "INSERT INTO abonos (id_venta, monto, metodo_pago, fecha) VALUES (?, ?, ?, ?)";
            
            $stmt = $this->pdo->prepare($sql);
            
            return $stmt->execute([
                (int)$data['id_venta'],
                $data['monto'],
                $data['metodo_pago'],
                $data['fecha']
            ]);

        } catch (PDOException $e) {
            error_log("Error en AbonoModel::create: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtiene todos los abonos de una venta específica.
     */
    public function getByVentaId($ventaId)
    {
        try {
            $sql = "SELECT * FROM abonos WHERE id_venta = ? ORDER BY fecha ASC";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([(int)$ventaId]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }

    /**
     * Calcula la suma total de lo pagado para una venta.
     * Útil para calcular el saldo pendiente.
     */
    public function getTotalPagado($ventaId)
    {
        try {
            $sql = "SELECT SUM(monto) as total FROM abonos WHERE id_venta = ?";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([(int)$ventaId]);
            $result = $stmt->fetch();
            return $result['total'] ?? 0.00;
        } catch (PDOException $e) {
            return 0.00;
        }
    }

    /**
     * Obtiene un abono específico por su ID.
     * (Necesario para rellenar el formulario de edición)
     */
    public function getById($id)
    {
        try {
            $stmt = $this->pdo->prepare("SELECT * FROM abonos WHERE id_abono = ?");
            $stmt->execute([(int)$id]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Actualiza un abono existente.
     * @param int $id ID del abono.
     * @param array $data Nuevos datos (monto, fecha).
     */
    public function update($id, $data)
    {
        try {
            $sql = "UPDATE abonos SET monto = ?, metodo_pago = ?, fecha = ? WHERE id_abono = ?";
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute([
                $data['monto'],
                $data['metodo_pago'],
                $data['fecha'],
                (int)$id
            ]);
        } catch (PDOException $e) {
            error_log("Error en AbonoModel::update: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Elimina un abono específico.
     */
    public function delete($id)
    {
        try {
            $stmt = $this->pdo->prepare("DELETE FROM abonos WHERE id_abono = ?");
            return $stmt->execute([(int)$id]);
        } catch (PDOException $e) {
            error_log("Error en AbonoModel::delete: " . $e->getMessage());
            return false;
        }
    }
}