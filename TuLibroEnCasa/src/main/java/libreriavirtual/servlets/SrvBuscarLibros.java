package libreriavirtual.servlets;

import java.io.IOException;
import java.util.List;
import javax.servlet.ServletException;
import javax.servlet.annotation.WebServlet;
import javax.servlet.http.*;

import libreriavirtual.bd.LibroBd;
import libreriavirtual.beans.Libro;
import libreriavirtual.config.GestorConfiguracion;

@WebServlet("/SrvBuscarLibros")
public class SrvBuscarLibros extends HttpServlet {
    private static final long serialVersionUID = 1L;

    @Override
    protected void doPost(HttpServletRequest request, HttpServletResponse response)
            throws ServletException, IOException {

        GestorConfiguracion.cargarConfiguracion();
        //Recuperamos la sesión
        HttpSession session = request.getSession();

        String titulo = request.getParameter("titulo");
        String autor = request.getParameter("autor");
        String categoria = request.getParameter("categoria");
        String logica = request.getParameter("logica");
        String orden = request.getParameter("orden");

        LibroBd bd = new LibroBd();
        List<Libro> lista = null;

        if (bd.abrirConexion()) {
            lista = bd.buscarLibros(titulo, autor, categoria, logica, orden);
            bd.cerrarConexion();
        }
        
        
        session.setAttribute("listaLibros", lista);

        request.setAttribute("listaLibros", lista);
        request.getRequestDispatcher("resultado_busqueda.jsp").forward(request, response);
    }
}


