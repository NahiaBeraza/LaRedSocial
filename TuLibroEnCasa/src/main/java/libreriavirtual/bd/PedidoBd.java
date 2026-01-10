package libreriavirtual.bd;

import java.sql.PreparedStatement;
import java.sql.ResultSet;
import java.sql.SQLException;
import java.sql.Statement;

public class PedidoBd extends BdBase {

    public PedidoBd() {
        super();
    }

    /**
     * Inserta un pedido y devuelve el id_pedido generado.
     */
    public int insertarPedido(int idUsuario, String estado) {

        int idGenerado = -1;

        String sql = "INSERT INTO pedido (id_usuario, fecha_pedido, estado) "
                   + "VALUES (?, CURDATE(), ?)";

        try {
            PreparedStatement ps = conexion.prepareStatement(sql, Statement.RETURN_GENERATED_KEYS);
            ps.setInt(1, idUsuario);
            ps.setString(2, estado);

            ps.executeUpdate();

            ResultSet rs = ps.getGeneratedKeys();
            if (rs.next()) {
                idGenerado = rs.getInt(1);
            }

        } catch (SQLException e) {
            e.printStackTrace();
        }

        return idGenerado;
    }
}
