package frontendSimple;

import javax.swing.*;
import java.awt.*;

public class MenuPrincipalSimple extends JFrame {

    private static final long serialVersionUID = 1L;

    public MenuPrincipalSimple() {

        setTitle("Librería Virtual");
        setSize(300, 250);
        setLocationRelativeTo(null);
        setDefaultCloseOperation(JFrame.EXIT_ON_CLOSE);

        setLayout(new GridLayout(5, 1, 10, 10));

        JLabel titulo = new JLabel("Librería Virtual", SwingConstants.CENTER);

        JButton btnAutores = new JButton("Gestionar autores");
        JButton btnCategorias = new JButton("Gestionar categorías");
        JButton btnEditoriales = new JButton("Gestionar editoriales");
        JButton btnLibros = new JButton("Gestionar libros");

        btnAutores.addActionListener(e -> new AutorUISimple());
        btnCategorias.addActionListener(e -> new CategoriaUISimple());
        btnEditoriales.addActionListener(e -> new EditorialUISimple());
        btnLibros.addActionListener(e -> new LibroUISimple());

        add(titulo);
        add(btnAutores);
        add(btnCategorias);
        add(btnEditoriales);
        add(btnLibros);

        setVisible(true);
    }

    // ============================================================
    // MAIN ADAPTADO CON FONDO A PANTALLA COMPLETA
    // ============================================================
    public static void main(String[] args) {

        // Ventana de fondo a pantalla completa
        JFrame fondo = new JFrame();
        fondo.setUndecorated(true);
        fondo.setExtendedState(JFrame.MAXIMIZED_BOTH);
        fondo.getContentPane().setBackground(Color.LIGHT_GRAY); // o Color.WHITE
        fondo.setVisible(true);

        // Ventana principal centrada encima
        MenuPrincipalSimple ventana = new MenuPrincipalSimple();
        ventana.setLocationRelativeTo(null);
        ventana.setVisible(true);

        // Cerrar el fondo cuando se cierre la ventana principal
        ventana.addWindowListener(new java.awt.event.WindowAdapter() {
            @Override
            public void windowClosing(java.awt.event.WindowEvent e) {
                fondo.dispose();
            }
        });
    }
}
