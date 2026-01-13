package libreriavirtual.servlets;

import libreriavirtual.beans.Carrito;
import libreriavirtual.beans.Libro;
//import libreriavirtual.beans.LineaCarrito;

import javax.servlet.ServletException;
import javax.servlet.annotation.WebServlet;
import javax.servlet.http.*;
import java.io.IOException;
import java.util.List;


@WebServlet("/SrvCarrito")
public class SrvCarrito extends HttpServlet {
	private static final long serialVersionUID = 1L;

	@Override
    protected void doPost(HttpServletRequest request, HttpServletResponse response)
            throws ServletException, IOException {

        HttpSession sesion = request.getSession();

        // Recuperar carrito o crearlo si no existe
        Carrito carrito = (Carrito) sesion.getAttribute("carrito");
        if (carrito == null) {
            carrito = new Carrito();
        }

        // Recuperar la lista de libros que se mostró en la búsqueda, si no ponemos el @SuppressWarnings("unchecked") indica error
        // ya que no sabe que lo que devuelve y con esto confirmamos que la variable listaLibros que recuperamos en SrvCarrito sí es una List<Libro> real
        @SuppressWarnings("unchecked")
        List<Libro> listaLibros = (List<Libro>) sesion.getAttribute("listaLibros");    

        if (listaLibros != null) {
            int index = 1;

            for (Libro libro : listaLibros) {

                String check = request.getParameter("libro" + index);
                String cantidadStr = request.getParameter("cantidad" + index);

                if (check != null && cantidadStr != null) {

                    int cantidad = Integer.parseInt(cantidadStr);

                    // Validación básica
                    if (cantidad < 1) cantidad = 1;
                    if (cantidad > libro.getStock()) cantidad = libro.getStock();

                    // Añadir o sustituir cantidad en el carrito
                    carrito.agregarLinea(libro, cantidad);
                }

                index++;
            }
        }

        // Guardar carrito actualizado en sesión
        sesion.setAttribute("carrito", carrito);

        // Redirigir al estado del carrito
        response.sendRedirect("estado_carrito.jsp");
    }
}
