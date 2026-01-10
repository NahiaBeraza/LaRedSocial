package libreriavirtual.servlets;

import java.io.IOException;
import javax.servlet.ServletException;
import javax.servlet.ServletConfig;
import javax.servlet.annotation.WebServlet;
import javax.servlet.http.HttpServlet;
import javax.servlet.http.HttpServletRequest;
import javax.servlet.http.HttpServletResponse;

import libreriavirtual.bd.TestBd;
import libreriavirtual.config.GestorConfiguracion;
/*probamos la conexion con http://localhost:8080/TuLibroEnCasa//SrvTestConexion en el navegador */

@WebServlet("/SrvTestConexion")
public class SrvTestConexion extends HttpServlet {
    private static final long serialVersionUID = 1L;

    @Override
    public void init(ServletConfig config) throws ServletException {
        super.init(config);

        // Ahora NO usamos rutas físicas
        boolean ok = GestorConfiguracion.cargarConfiguracion();

        if (ok) {
            System.out.println("Configuración cargada correctamente.");
        } else {
            System.out.println("Error cargando configuración.");
        }
    }

    @Override
    protected void doGet(HttpServletRequest request, HttpServletResponse response)
            throws ServletException, IOException {

        TestBd bd = new TestBd();

        boolean abierta = bd.abrirConexion();

        if (abierta) {
            response.getWriter().println("Bien Conexión a MySQL OK");
            bd.cerrarConexion();
        } else {
            response.getWriter().println("Error al conectar con MySQL");
        }
    }
}
