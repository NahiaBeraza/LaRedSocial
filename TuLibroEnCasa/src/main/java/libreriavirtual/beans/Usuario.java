package libreriavirtual.beans;

import java.io.Serializable;
import java.util.Date;

public class Usuario implements Serializable {
	/* Inplementado la interfaz serializable porque en las aplicaciones java los objetos que se guardan en sesión (HttpSession), 
	 * contexto de aplicación (ServletContext), atributos que viajan entre servidores en clúster, atributos qeu tomcat puede persistir en disco.
	 * DEBEN SER SERIALIZABLES (
	 * lo que significa que el objeto puede convertirse en una secuencia de bytes para guardarlo en disco, enviarlo por la red, 
	 * replicarlo entre nodos de un clúster, restaurarlo después de reiniciar el servidor.
	 * tomcat serializa automáticamente la sesión cuando: 
	 * se reinicia el servidor, se despliega de nuevo la aplicación, se usa un clúster con balanceo de carga.
	 * SI EN LA SESIÓN HAY OBJETOS NO SERIALIZABLES TOMCAT LANZA ERRORES.
	 * Si el bean solo se usa en el request por ejemplo request.setAttribute("clientes", listaClientes); 
	 * y no se guarda en sesión, entonces no es obligatorio, pero aun así, emproyectos profesionales se hace SIEMPRE serializable 
	 * por buenas prácticas, evitar errores futuros y conpatilbilidad con conteredores java EE.
	 * en este caso vamos a guardar objetos en sesión, por ejemplo sesion.setAttribute("user", user); 
	 * y guardaremos el carrito y datos temporales del pedido por lo que ponemos todos los beans con implementacion Serializable */
    private static final long serialVersionUID = 1L;

    private int idUsuario;
    private String nombre;
    private String apellido1;
    private String apellido2;
    private String dni;
    private String direccion;
    private Date fechaNacimiento;
    private String email;
    private String usuario;
    private String clave;

    	/* poner un constructor vacío siempre*/
    public Usuario() {}

    public Usuario(int idUsuario, String nombre, String apellido1, String apellido2,
                   String dni, String direccion, Date fechaNacimiento,
                   String email, String usuario, String clave) {
        this.idUsuario = idUsuario;
        this.nombre = nombre;
        this.apellido1 = apellido1;
        this.apellido2 = apellido2;
        this.dni = dni;
        this.direccion = direccion;
        this.fechaNacimiento = fechaNacimiento;
        this.email = email;
        this.usuario = usuario;
        this.clave = clave;
    }

    public int getIdUsuario() { return idUsuario; }
    public void setIdUsuario(int idUsuario) { this.idUsuario = idUsuario; }

    public String getNombre() { return nombre; }
    public void setNombre(String nombre) { this.nombre = nombre; }

    public String getApellido1() { return apellido1; }
    public void setApellido1(String apellido1) { this.apellido1 = apellido1; }

    public String getApellido2() { return apellido2; }
    public void setApellido2(String apellido2) { this.apellido2 = apellido2; }

    public String getDni() { return dni; }
    public void setDni(String dni) { this.dni = dni; }

    public String getDireccion() { return direccion; }
    public void setDireccion(String direccion) { this.direccion = direccion; }

    public Date getFechaNacimiento() { return fechaNacimiento; }
    public void setFechaNacimiento(Date fechaNacimiento) { this.fechaNacimiento = fechaNacimiento; }

    public String getEmail() { return email; }
    public void setEmail(String email) { this.email = email; }

    public String getUsuario() { return usuario; }
    public void setUsuario(String usuario) { this.usuario = usuario; }

    public String getClave() { return clave; }
    public void setClave(String clave) { this.clave = clave; }
}
