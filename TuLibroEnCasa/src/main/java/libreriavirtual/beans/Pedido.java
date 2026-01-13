package libreriavirtual.beans;

import java.io.Serializable;
import java.util.Date;

public class Pedido implements Serializable {
    private static final long serialVersionUID = 1L;

    private int idPedido;
    private int idUsuario;
    private Date fechaPedido;
    private String estado;

    public Pedido() {}

    public Pedido(int idPedido, int idUsuario, Date fechaPedido, String estado) {
        this.idPedido = idPedido;
        this.idUsuario = idUsuario;
        this.fechaPedido = fechaPedido;
        this.estado = estado;
    }

    public int getIdPedido() {
        return idPedido;
    }

    public void setIdPedido(int idPedido) {
        this.idPedido = idPedido;
    }

    public int getIdUsuario() {
        return idUsuario;
    }

    public void setIdUsuario(int idUsuario) {
        this.idUsuario = idUsuario;
    }

    public Date getFechaPedido() {
        return fechaPedido;
    }

    public void setFechaPedido(Date fechaPedido) {
        this.fechaPedido = fechaPedido;
    }

    public String getEstado() {
        return estado;
    }

    public void setEstado(String estado) {
        this.estado = estado;
    }
}
