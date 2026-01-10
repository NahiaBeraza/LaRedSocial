package libreriavirtual.beans;

import java.io.Serializable;

public class LineaCarrito implements Serializable {
    private static final long serialVersionUID = 1L;

    private Libro libro;
    private int cantidad;

    public LineaCarrito() {}

    public LineaCarrito(Libro libro, int cantidad) {
        this.libro = libro;
        this.cantidad = cantidad;
    }

    public Libro getLibro() {
        return libro;
    }

    public void setLibro(Libro libro) {
        this.libro = libro;
    }

    public int getCantidad() {
        return cantidad;
    }

    public void setCantidad(int cantidad) {
        this.cantidad = cantidad;
    }

    public double getSubtotal() {
        return libro.getPrecioUnitario() * cantidad;
    }
}
