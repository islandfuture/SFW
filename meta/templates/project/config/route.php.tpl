<?php
return array(
    /*

    //  @example   /blog/
    //  @example   /blog/12/
     

    'blog' => array(
        ':num:' => array(
            '+' => 'idPost',
            '=>' => array(
                ':end:' => 'detail'
            )
        ),
        ':end:' => 'index'
    ),

    
    //  @example   /courses/12/lessons/1/ -> /courses/lessons/detail.php?idCourse=12&idLesson=1
    //  @example   /courses/12/1/ -> /courses/lessons/detail.php?idCourse=12&idLesson=1

    'courses' => array(
        ':num:' => array(
            '+' => 'idCourse',
            '=>' => array(
                'lessons' => array(
                    ':num:' => array(
                        '+' => 'idLesson',
                        '=>' => array(
                            ':end:' => 'detail'
                        )
                    )
                ),
                ':end:' => 'detail'
            ),
            ':end:' => 'detail'
        ),
        ':end:' => 'index'
    )
    
    */
);