package libreriavirtual.beans;

import java.io.Serializable;
import java.util.ArrayList;
import java.util.List;

public class Carrito implements Serializable {
    private static final long serialVersionUID = 1L;

    private List<LineaCarrito> lineas;

    public Carrito() {
        lineas = new ArrayList<>();
    }

    public List<LineaCarrito> getLineas() {
        return lineas;
    }

    public void agregarLinea(Libro libro, int cantidad) {
        // Si el libro ya está en el carrito, sustituimos la cantidad
        for (LineaCarrito lc : lineas) {
            if (lc.getLibro().getIsbn().equals(libro.getIsbn())) {
                lc.setCantidad(cantidad); // sustituir, no sumar
                return;
            }
        }
        // Si no está, lo añadimos como nueva línea
        lineas.add(new LineaCarrito(libro, cantidad));
    }

    public void eliminarPorIsbn(String isbn) {
        lineas.removeIf(lc -> lc.getLibro().getIsbn().equals(isbn));
    }

    public void modificarCantidad(String isbn, int nuevaCantidad) {
        for (LineaCarrito lc : lineas) {
            if (lc.getLibro().getIsbn().equals(isbn)) {
                lc.setCantidad(nuevaCantidad);
                return;
            }
        }
    }

    public double getTotal() {
        double total = 0;
        for (LineaCarrito lc : lineas) {
            total += lc.getSubtotal();
        }
        return total;
    }

    public boolean estaVacio() {
        return lineas.isEmpty();
    }
}

