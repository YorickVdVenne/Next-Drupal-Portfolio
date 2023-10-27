import React from 'react'
import styles from './styles.module.css'
import { Swiper, SwiperSlide } from 'swiper/react'
import { Pagination, A11y } from 'swiper/modules';
import { useSwiper } from "swiper/react";

import type { SwiperOptions } from 'swiper/types';

import 'swiper/css';
import 'swiper/css/pagination';
import { Arrow } from '@components/atoms/Icons/Component';

export interface SliderProps {
  children: React.ReactElement[]
  className?: string
  navigation?: boolean
  spaceBetween?: number
  breakpoints?: {
    [width: number]: SwiperOptions
    [ratio: string]: SwiperOptions
  }
}

export function Slider (props: SliderProps): JSX.Element {
  

  return (
    <Swiper
      modules={[ Pagination, A11y]}
      spaceBetween={50}
      slidesPerView={1}
      pagination={{ clickable: true }}
      loop
      className={styles.slider}
    >
      <SwiperButtonPrev />
      <SwiperButtonNext />
      {props.children.map((child, key) => {
        return <SwiperSlide key={key} className={styles.swiperSlide}>{child}</SwiperSlide>
      })}
    </Swiper>
  )
}

const SwiperButtonPrev = () => {
  const swiper = useSwiper();
  return <button className={styles.prevButton} onClick={() => swiper.slidePrev()}><Arrow /></button>;
};

const SwiperButtonNext = () => {
  const swiper = useSwiper();
  return <button className={styles.nextButton} onClick={() => swiper.slideNext()}><Arrow /></button>;
};