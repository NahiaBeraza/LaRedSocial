package libreriavirtual.servlets;

import libreriavirtual.beans.Carrito;
import libreriavirtual.beans.LineaCarrito;
import libreriavirtual.beans.Libro;

import javax.servlet.ServletException;
import javax.servlet.annotation.WebServlet;
import javax.servlet.http.*;
import java.io.IOException;
import java.util.Iterator;

@WebServlet("/SrvActualizarCarrito")
public class SrvActualizarCarrito extends HttpServlet {
    private static final long serialVersionUID = 1L;

    @Override
    protected void doPost(HttpServletRequest request, HttpServletResponse response)
            throws ServletException, IOException {

        HttpSession sesion = request.getSession();
        Carrito carrito = (Carrito) sesion.getAttribute("carrito");

        if (carrito == null) {
            response.sendRedirect("estado_carrito.jsp");
            return;
        }

        int index = 1;

        // Usamos Iterator para evitar ConcurrentModificationException
        Iterator<LineaCarrito> it = carrito.getLineas().iterator();

        while (it.hasNext()) {
            LineaCarrito lc = it.next();
            Libro libro = lc.getLibro();
            //String isbn = libro.getIsbn(); // reservado por si se necesita en el futuro

            // Checkbox de eliminar
            String eliminar = request.getParameter("eliminar" + index);

            if (eliminar != null) {
                // Eliminar la línea de forma segura
                it.remove();
            } else {
                // Modificar cantidad
                String cantidadStr = request.getParameter("cantidad" + index);

                if (cantidadStr != null) {
                    int nuevaCantidad = Integer.parseInt(cantidadStr);

                    // Validaciones
                    if (nuevaCantidad < 1) nuevaCantidad = 1;
                    if (nuevaCantidad > libro.getStock()) nuevaCantidad = libro.getStock();

                    lc.setCantidad(nuevaCantidad);
                }
            }

            index++;
        }

        // Guardar carrito actualizado
        sesion.setAttribute("carrito", carrito);

        // Volver al carrito
        response.sendRedirect("estado_carrito.jsp");
    }
}
