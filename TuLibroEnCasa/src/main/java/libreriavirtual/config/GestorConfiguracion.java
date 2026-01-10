package libreriavirtual.config;

import java.io.InputStream;
import java.util.Properties;

public class GestorConfiguracion {

    public static boolean cargarConfiguracion() {
        boolean correcto = true;

        try {
            Configuracion configuracion = Configuracion.getInstancia();

            // Cargar el archivo desde el classpath (src/main/resources)
            InputStream is = GestorConfiguracion.class
                    .getClassLoader()
                    .getResourceAsStream("tulibroencasa.properties");

            if (is == null) {
                System.out.println("ERROR: No se encontró tulibroencasa.properties en src/main/resources");
                return false;
            }

            Properties propiedades = new Properties();
            propiedades.load(is);
            is.close();

            configuracion.setDriver(propiedades.getProperty("driver"));
            configuracion.setUrl(propiedades.getProperty("url"));
            configuracion.setUser(propiedades.getProperty("user"));
            configuracion.setPassword(propiedades.getProperty("password"));

        } catch (Exception e) {
            correcto = false;
            e.printStackTrace();
        }

        return correcto;
    }
}

