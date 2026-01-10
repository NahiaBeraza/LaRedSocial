package libreriavirtual.bd;

import java.sql.PreparedStatement;
import java.sql.SQLException;

import libreriavirtual.beans.LineaCarrito;

public class DetallePedidoBd extends BdBase {

    public DetallePedidoBd() {
        super();
    }

    /**
     * Inserta una línea del pedido en la tabla detallepedido.
     */
    public void insertarDetalle(int idPedido, LineaCarrito lc) {

        String sql = "INSERT INTO detallepedido (id_pedido, isbn, cantidad, precio_unitario, subtotal) "
                   + "VALUES (?, ?, ?, ?, ?)";

        try {
            PreparedStatement ps = conexion.prepareStatement(sql);

            ps.setInt(1, idPedido);
            ps.setString(2, lc.getLibro().getIsbn());
            ps.setInt(3, lc.getCantidad());
            ps.setDouble(4, lc.getLibro().getPrecioUnitario());
            ps.setDouble(5, lc.getSubtotal());

            ps.executeUpdate();

        } catch (SQLException e) {
            e.printStackTrace();
        }
    }
}

