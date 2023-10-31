import React from 'react'
import styles from './styles.module.css'
import { Swiper, SwiperSlide } from 'swiper/react'
import { Pagination } from 'swiper/modules';
import { useSwiper } from "swiper/react";

import 'swiper/css';
import 'swiper/css/pagination';

import { Arrow } from '@components/atoms/Icons/Component';

export interface SliderProps {
  children: React.ReactElement[]
  activeItemIndex: React.Dispatch<React.SetStateAction<number>>
}

export function Slider (props: SliderProps): JSX.Element {

  return (
    <div className={styles.sliderWrapper}>
      <Swiper
        modules={[ Pagination ]}
        spaceBetween={50}
        slidesPerView={1}
        pagination={{ clickable: true }}
        onSlideChange={(swiper) => props.activeItemIndex(swiper.realIndex)}
        className={styles.slider}
        loop
      >
        <SwiperButtonPrev />
        <SwiperButtonNext />
        {props.children.map((child, key) => {
          return <SwiperSlide key={key} className={styles.swiperSlide}>{child}</SwiperSlide>
        })}
      </Swiper>
    </div>
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