package org.hopto.fundacioncgr.fundacioncgr.sqlite;

import android.content.Context;
import android.database.Cursor;
import android.database.sqlite.SQLiteDatabase;
import android.database.sqlite.SQLiteDoneException;
import android.database.sqlite.SQLiteStatement;

import org.hopto.fundacioncgr.fundacioncgr.Pojo.Elemento;
import org.hopto.fundacioncgr.fundacioncgr.Pojo.Recorrido;

import java.util.ArrayList;
import java.util.Iterator;
import java.util.List;


/**
 * Clase fachada para usar la base de datos SQLite desde la aplicación.
 */
public class SQLWrapper {
    private AdminSQLiteHelper adb;

    /**
     * Constructor del Wrapper de la base de datos.
     * @param c contexto desde donde se crea el wrapper.
     */
    public SQLWrapper(Context c) {
        adb = new AdminSQLiteHelper(c);
    }

    /**
     * Método que se ejecuta cuando el recolector de basura libera la memoria del objeto.
     * @throws Throwable
     */
    @Override
    protected void finalize() throws Throwable {
        close();
    }

    /**
     * Método para establecer la conexión a la base de datos en modo escritura.
     * @return conexión a la base datos.
     */
    private SQLiteDatabase openWrite(){
        return adb.getWritableDatabase();
    }

    /**
     * Método para establecer la conexión a la base de datos en modo lectura.
     * @return conexión a la base datos.
     */
    private SQLiteDatabase openRead(){
        return adb.getReadableDatabase();
    }

    /**
     * Método para cerrar la conexión a la base de datos.
     */
    public void close(){
        adb.close();
    }

    /**
     *  Método para obtener el tiempo en donde se quedó reproduciendo una url concreta.
     * @param url url del contenido.
     * @return milisegundo donde se quedó la reproducción del contenido, por defecto 0.
     */
    public int getTimestamp(String url) {
        SQLiteDatabase db = this.openRead();

        String sql = "SELECT timestamp FROM reproducir_contenido WHERE url=?";

        SQLiteStatement statement = db.compileStatement(sql);

        statement.bindString(1, url);

        int timestamp = 0;

        try {
            timestamp = (int) statement.simpleQueryForLong();
        } catch (SQLiteDoneException e) {

        }

        db.close();

        return timestamp;
    }

    /**
     * Método para añadir una guía personalizada a la lista que tiene que descargar al mostrar los recorridos.
     * @param code codigo de la guía
     * @return true si se ha añadido con exito, false en otro caso.
     */
    public boolean setGuiaPersonalizada(String code){
        SQLiteDatabase db = this.openWrite();

        String sql = "INSERT OR REPLACE INTO codigosguiaspersonalizadas(`#guia`) VALUES(?)";

        SQLiteStatement statement = db.compileStatement(sql);

        statement.bindString(1, code);


        long result = statement.executeInsert();
        db.close();

        return ((result != -1) ? true:false);
    }

    /**
     * Método para añadir una guía personalizada a la lista que tiene que descargar al mostrar los recorridos.
     * @param code codigo de la guía
     * @return true si se ha añadido con exito, false en otro caso.
     */
    public boolean removeGuiaPersonalizadaLocal(String code){
        SQLiteDatabase db = this.openWrite();

        String sql_compuesta = "DELETE FROM Compuesta WHERE `#guia`=?";
        String sql_guia = "DELETE FROM guiapersonalizada WHERE `#guia`=?";

        SQLiteStatement statement_compuesta = db.compileStatement(sql_compuesta);
        SQLiteStatement statement_guia = db.compileStatement(sql_guia);

        statement_compuesta.bindString(1, code);
        statement_guia.bindString(1, code);


        int result = statement_guia.executeUpdateDelete();
        result &= statement_compuesta.executeUpdateDelete();
        db.close();

        return ((result != -1) ? true:false);
    }

    /**
     * Método para obtener los códigos de las guías personalizadas para descargar.
     * @return lista con los códigos de las guías.
     */
    public List<String> getGuiasPersonalizadas(){
        List<String> codigos = new ArrayList<>();

        SQLiteDatabase db = this.openRead();

        String sql = "SELECT `#guia` AS codigo FROM codigosguiaspersonalizadas";

        Cursor cursor = db.rawQuery(sql, null);

        try{
            while(cursor.moveToNext()){
                codigos.add(cursor.getString(0));
            }
        }finally {
            db.close();
        }

        return codigos;
    }

    /**
     * Método para obtener las guías almacenadas en las base de datos local.
     * @return lista con las guías.
     */
    public List<Recorrido> getGuiasLocales(){
        List <Recorrido> recorridos = new ArrayList<>();

        SQLiteDatabase db = this.openRead();

        String sql = "SELECT `#guia`, `nombre_guia` FROM guiapersonalizada";

        Cursor cursor = db.rawQuery(sql, null);

        if(cursor.getCount()!=0) {
            try {
                while (cursor.moveToNext()){
                    Recorrido r = new Recorrido(cursor.getString(0), cursor.getString(1), true);
                    recorridos.add(r);
                }
            } finally {
                db.close();
            }
        }

        return recorridos;
    }

    /**
     * Método para obtener los contenidos de las guías almacenadas en las base de datos local.
     * @return lista con elementos de una guia.
     */
    public List<Elemento> getContenidosLocales(String code){
        List <Elemento> elementos = new ArrayList<>();

        SQLiteDatabase db = this.openRead();

        String sql = "SELECT r.`#recurso`, r.`codigo_qr` ,r.`nombre` FROM Compuesta c, Recursos r WHERE c.`#guia`=? AND c.`#recurso`=r.`#recurso`";


        Cursor cursor = db.rawQuery(sql,  new String[] {code});

        if(cursor.getCount()!=0) {
            try {
                while (cursor.moveToNext()){
                    Elemento e = new Elemento(cursor.getString(0), cursor.getString(1), cursor.getString(2));
                    elementos.add(e);
                }
            } finally {
                db.close();
            }
        }

        return elementos;
    }



    /**
     * Método para alamacenar el tiempo donde se quedó un un contenido.
     * @param url url del contenido que queremos alamacenar
     * @param timestamp milisegundos de donde se quedó reproduciendo el contenido.
     * @return true si se ha almacenado con éxito, false en caso contrario.
     */
    public boolean setTimestamp(String url, int timestamp) {
        SQLiteDatabase db = this.openWrite();

        String sql = "INSERT OR REPLACE INTO reproducir_contenido(url, timestamp) VALUES(?,?)";

        SQLiteStatement statement = db.compileStatement(sql);

        statement.bindString(1, url);
        statement.bindLong(2, timestamp);

        long result = statement.executeInsert();
        db.close();

        return ((result != -1) ? true:false);
    }

    /**
     * Método para almacenar una guía personalizada de forma local.
     * @param tituloGuia titulo de la guía personalizada.
     * @param elementos elementos que forman la guía.
     * @return true si se ha almacenado con éxito, false en caso contrario.
     */
    public boolean setGuia(String tituloGuia, List<Elemento> elementos){
        SQLiteDatabase db = this.openWrite();

        String sentencia1 = "INSERT INTO guiapersonalizada (nombre_guia) values(?);";
        String sentencia2 = "INSERT INTO compuesta (`#recurso`, `#guia`) values(?,?);";
        String sentencia3 = "INSERT OR IGNORE INTO recursos (`#recurso`, codigo_qr, nombre) values (?,?,?);";

        SQLiteStatement statement = db.compileStatement(sentencia1);
        statement.bindString(1,tituloGuia);
        long result = statement.executeInsert();

        if(result != -1){
            try {
                //obtener valor de la última guia creada
                String guia = "select last_insert_rowid()";
                SQLiteStatement statement4 = db.compileStatement(guia);
                int valorguia = (int) statement4.simpleQueryForLong();


                //una vez obtenido el valor de la guía creada, añadir resto de las tablas
                SQLiteStatement statement2 = db.compileStatement(sentencia2);
                SQLiteStatement statement3 = db.compileStatement(sentencia3);

                statement2.bindLong(2, valorguia);
                for(Elemento e : elementos){
                    statement2.bindLong(1,Integer.valueOf(e.getId()));
                    statement3.bindLong(1,Integer.valueOf(e.getId()));
                    statement3.bindLong(2,Integer.valueOf(e.getCodigo()));
                    statement3.bindString(3,e.getNombre());

                    long result2 = statement2.executeInsert();
                    long result3 = statement3.executeInsert();
                }

            }catch (SQLiteDoneException S){

            }


        }


        return  true;
    }
}
