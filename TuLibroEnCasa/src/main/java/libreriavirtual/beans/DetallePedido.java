package libreriavirtual.beans;

import java.io.Serializable;

public class DetallePedido implements Serializable {
    private static final long serialVersionUID = 1L;

    private int idDetalle;
    private int idPedido;
    private String isbn;
    private int cantidad;
    private double precioUnitario;
    private double subtotal;

    public DetallePedido() {}

    public DetallePedido(int idDetalle, int idPedido, String isbn, int cantidad,
                         double precioUnitario, double subtotal) {
        this.idDetalle = idDetalle;
        this.idPedido = idPedido;
        this.isbn = isbn;
        this.cantidad = cantidad;
        this.precioUnitario = precioUnitario;
        this.subtotal = subtotal;
    }

    public int getIdDetalle() {
        return idDetalle;
    }

    public void setIdDetalle(int idDetalle) {
        this.idDetalle = idDetalle;
    }

    public int getIdPedido() {
        return idPedido;
    }

    public void setIdPedido(int idPedido) {
        this.idPedido = idPedido;
    }

    public String getIsbn() {
        return isbn;
    }

    public void setIsbn(String isbn) {
        this.isbn = isbn;
    }

    public int getCantidad() {
        return cantidad;
    }

    public void setCantidad(int cantidad) {
        this.cantidad = cantidad;
    }

    public double getPrecioUnitario() {
        return precioUnitario;
    }

    public void setPrecioUnitario(double precioUnitario) {
        this.precioUnitario = precioUnitario;
    }

    public double getSubtotal() {
        return subtotal;
    }

    public void setSubtotal(double subtotal) {
        this.subtotal = subtotal;
    }
}

