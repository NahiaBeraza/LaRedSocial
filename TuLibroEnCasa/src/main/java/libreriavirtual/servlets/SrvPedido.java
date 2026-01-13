package libreriavirtual.servlets;

import libreriavirtual.beans.Carrito;
import libreriavirtual.beans.LineaCarrito;
import libreriavirtual.beans.Usuario;
import libreriavirtual.bd.PedidoBd;
import libreriavirtual.bd.DetallePedidoBd;
import libreriavirtual.bd.LibroBd;

import javax.servlet.ServletException;
import javax.servlet.annotation.WebServlet;
import javax.servlet.http.*;
import java.io.IOException;

@WebServlet("/SrvPedido")
public class SrvPedido extends HttpServlet {
    private static final long serialVersionUID = 1L;

    @Override
    protected void doPost(HttpServletRequest request, HttpServletResponse response)
            throws ServletException, IOException {

        HttpSession sesion = request.getSession();

        Usuario u = (Usuario) sesion.getAttribute("usuarioObj");
        Carrito carrito = (Carrito) sesion.getAttribute("carrito");

        // Validaciones básicas
        if (u == null) {
            response.sendRedirect("index.jsp");
            return;
        }

        if (carrito == null || carrito.estaVacio()) {
            response.sendRedirect("estado_carrito.jsp");
            return;
        }

        // Estado del pedido
        String estado = "confirmado";

        // Creamos los objetos BD
        PedidoBd pedidoBd = new PedidoBd();
        DetallePedidoBd detalleBd = new DetallePedidoBd();
        LibroBd libroBd = new LibroBd();

        try {
            // 1. Abrir UNA sola conexión compartida
            pedidoBd.abrirConexion();
            detalleBd.setConexion(pedidoBd.getConexion()); // Compartir conexión
            libroBd.setConexion(pedidoBd.getConexion());   // Compartir conexión

            // 2. Iniciar transacción
            pedidoBd.abrirTransaccion();

            // 3. Insertar pedido
            int idPedido = pedidoBd.insertarPedido(u.getIdUsuario(), estado);

            // Guardar idPedido en sesión para final.jsp
            sesion.setAttribute("idPedido", idPedido);

            // 4. Insertar detalles y restar stock
            for (LineaCarrito lc : carrito.getLineas()) {
                detalleBd.insertarDetalle(idPedido, lc);
                libroBd.restarStock(lc.getLibro().getIsbn(), lc.getCantidad());
            }

            // 5. Confirmar transacción
            pedidoBd.hacerCommit();

            // 6. Vaciar carrito
            sesion.removeAttribute("carrito");

            // 7. Redirigir a página final
            response.sendRedirect("final.jsp");

        } catch (Exception e) {

            // Si algo falla → rollback
            pedidoBd.hacerRollback();
            e.printStackTrace();
            response.sendRedirect("error.jsp");

        } finally {
            // Cerrar conexión
            pedidoBd.cerrarConexion();
        }
    }
}
