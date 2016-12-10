CREATE DATABASE IF NOT EXISTS `test_fifedu` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;

USE `test_fifedu`;

CREATE TABLE IF NOT EXISTS `fifedu_teacher` (
  `id` INT NOT NULL COMMENT '序号',
  `uid` VARCHAR(20) NULL COMMENT '唯一标识',
  `username` VARCHAR(255) NULL COMMENT '用户名',
  `card_num` VARCHAR(255) NULL COMMENT '工号',
  `name` VARCHAR(255) NULL COMMENT '姓名',
  `college_uid` VARCHAR(20) NULL COMMENT '院系uid',
  `college_name` VARCHAR(255) NULL COMMENT '院系',
  PRIMARY KEY (`id`)
) ENGINE = MyISAM CHARSET=utf8 COLLATE utf8_general_ci COMMENT = '教师信息表';

CREATE TABLE IF NOT EXISTS `fifedu_student` (
  `id` INT NOT NULL COMMENT '序号',
  `uid` VARCHAR(20) NULL COMMENT '唯一标识',
  `username` VARCHAR(255) NULL COMMENT '用户名',
  `card_num` VARCHAR(255) NULL COMMENT '学号',
  `name` VARCHAR(255) NULL COMMENT '姓名',
  `grade` VARCHAR(255) NULL COMMENT '年级',
  `college_uid` VARCHAR(20) NULL COMMENT '院系uid',
  `college_name` VARCHAR(255) NULL COMMENT '院系',
  `class1_uid` VARCHAR(20) NULL COMMENT '自然班uid',
  `class1_name` VARCHAR(255) NULL COMMENT '自然班',
  `class2_uid` VARCHAR(20) NULL COMMENT '教学班uid',
  `class2_name` VARCHAR(255) NULL COMMENT '教学班',
  PRIMARY KEY (`id`)
) ENGINE = MyISAM CHARSET=utf8 COLLATE utf8_general_ci COMMENT = '学生信息表';

CREATE TABLE `fifedu_class1` (
  `id` INT NOT NULL COMMENT '序号',
  `uid` VARCHAR(20) NULL COMMENT '唯一标识',
  `name` VARCHAR(255) NULL COMMENT '班级名称',
  `grade` VARCHAR(255) NULL COMMENT '年级',
  `student_count` INT NULL COMMENT '学生人数',
  `college_uid` VARCHAR(20) NULL COMMENT '院系uid',
  `college_name` VARCHAR(255) NULL COMMENT '院系',
  PRIMARY KEY (`id`)
) ENGINE = MyISAM CHARSET=utf8 COLLATE utf8_general_ci COMMENT = '自然班信息表';

CREATE TABLE `fifedu_class2` (
  `id` INT NOT NULL COMMENT '序号',
  `uid` VARCHAR(20) NULL COMMENT '唯一标识',
  `name` VARCHAR(255) NULL COMMENT '班级名称',
  `grade` VARCHAR(255) NULL COMMENT '年级',
  `student_count` INT NULL COMMENT '学生人数',
  `college_uid` VARCHAR(20) NULL COMMENT '院系uid',
  `college_name` VARCHAR(255) NULL COMMENT '院系',
  `teacher_uid` VARCHAR(20) NULL COMMENT '授课教师uid',
  `teacher_name` VARCHAR(255) NULL COMMENT '授课教师',
  PRIMARY KEY (`id`)
) ENGINE = MyISAM CHARSET=utf8 COLLATE utf8_general_ci COMMENT = '教学班信息表';

CREATE TABLE `fifedu_college` (
  `id` INT NOT NULL COMMENT '序号',
  `uid` VARCHAR(255) NULL COMMENT '唯一标识',
  `name` VARCHAR(255) NULL COMMENT '院系名称',
  `class1_count` VARCHAR(255) NULL COMMENT '自然班数',
  PRIMARY KEY (`id`)
) ENGINE = MyISAM CHARSET=utf8 COLLATE utf8_general_ci COMMENT = '院系信息表';
