-- Ya NO es necesario este script
-- El problema era de ortografía, no de tamaño de columna
-- La base de datos espera 'autorrefractometro' (con doble rr)
-- El código se corrigió para enviar 'autorrefractometro'

-- Si quieres verificar los valores permitidos en la columna tipo:
SHOW COLUMNS FROM graduaciones WHERE Field='tipo';
